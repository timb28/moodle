<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Support functions for component 'block_istart_reports', language 'en'
 *
 * @package   block_istart_reports
 * @copyright Harcourts Academy <academy@harcourts.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* NOTE!!!!!!!!
 * Refer to https://moodle.org/mod/forum/discuss.php?d=91370 when saving changed
 * manager's email addresses.
 *
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/coursecatlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

define('BLOCK_NAME', 'istart_reports');
define('NUMPASTREPORTDAYS', 6);
define('MANAGERREPORT', 1);

/**
 * Function to be run periodically according to the scheduled task.
 *
 * Finds all the iStart reports that have yet to be mailed out, and mails them
 * out to all managers as well as other maintance tasks.
 *
 */
function istart_reports_cron() {

    clean_reports();
    queue_manager_reports();
    process_manager_reports();


    return true;
}

/**
 * Cleans manager report queue
 * @return true or error
 */
function clean_reports() {
    global $DB;

    $oldreportconditions = 'reporttime IS NULL or reporttime < '.(time() - YEARSECS);

    // Clean store of manager report emails sent and reports processed for past students (sent > six months ago)
    $DB->delete_records_select('block_istart_reports_queue', $oldreportconditions);

    // Clean store of manager report emails sent for past students no longer enrolled
}

/**
 * Queues manager reports for all intake groups for later sending
 * @return true
 */
function queue_manager_reports() {
    
    core_php_time_limit::raise(300); // terminate if not able to process manager reports in 5 minutes

    // Record in the Moodle event log that istart manager reports have started processing

    // Get istart courses that have this block
    $courses = get_courses_with_block(get_blockid(BLOCK_NAME));
    
    foreach ($courses as $course) {
        error_log("1. Started processing reports for course: $course->id");

        // Get all current istart intakes as an array containing the $group->idnumber for each intake
        $groups = groups_get_all_groups($course->id);

        foreach ($groups as $group) {
            // Check whether the group is valid for istart
            if (!is_group_valid($group)) {
                error_log("Cannot process iStart manager report for group: $group->id ($group->name) "
                        . "the group id number '$group->idnumber' is not a valid iStart start date.");
                continue;
            }

            queue_manager_report_for_group($course->id, $group);
        }
    }

    return true;
}

/**
 * Sends istart manager reports for a given istart intake group
 * @param int $courseid ID of the course.
 * @param stdClass $group the group to process.
 * @return true if a report was sent
 */
function queue_manager_report_for_group($courseid, $group) {
    // Send out all unsent manager reports from the last six days.
    // Reports older than 6 days will not be mailed.  This is to avoid the problem where
    // cron has not been running for a long time or a student moves iStart group,
    // and then suddenly people are flooded with mail from the past few weeks or months
    $daysago = 0;

    while ($daysago <= NUMPASTREPORTDAYS) {
        $reporttime = time() - (DAYSECS * $daysago); //TODO: fix the time to UMT 0:00 on the day otherwise this changes too often
        error_log("2. Started processing group: $group->id ($group->name),  Days ago: $daysago, Report time: $reporttime");
        queue_manager_report_for_group_on_date($courseid, $group, $reporttime);
        $daysago++;
    }
}

/**
 * Sends istart manager reports for a given istart intake group
 * @param int $courseid ID of the course.
 * @param stdClass $group the group to be processed.
 * @param string $reporttime The report date as a timestamp.
 * @return true if a report was sent [TODO: will it?]
 */
function queue_manager_report_for_group_on_date($courseid, $group, $reporttime) {
    $starttime = strtotime($group->idnumber);

    // Is there a manager report for the group to send on the given report date?
    if (!is_istart_report_date($starttime,$reporttime)) {
        return false;
    }
    // If yes, was the report processed?
    // If yes, task complete: continue
    // If no, istart_process_manager_report($intake, $reportdate)
    error_log("Started processing reports for group: $group->id ($group->name)");

    // Get a list of all users in the group who:
    //  - are students
    //  - have a manager email (user profile field 'manageremail')
    //  - have finished an iStart week recently (after start date + 6 and before start date + 174)

    // Send the manager report for every student in that list
    // istart_send_manager_report($courseid, $groupid, $userid, $reportdate);
    $groupmembers = groups_get_members($group->id);

    foreach ($groupmembers as $user) {
        istart_queue_manager_report_for_user_on_date($courseid, $group->id, $user, $reporttime);

    }

    // Store that the manager report for the group on the given report date has been processed

    // Record in the Moodle event log that istart manager reports have been sent
    // for the groupid and report date

    return true;
}

/**
 * Queues an istart user a manager report for later sending.
 * @param int $courseid course ID of the istart course.
 * @param int $groupid ID of the user's group.
 * @param stdClass $user user being reported on.
 * @param string $reporttime The report date as a timestamp.
 * @return true or error
 */
function istart_queue_manager_report_for_user_on_date($courseid, $groupid, $user, $reporttime) {
    global $DB;
    error_log(" - Queueing manager report for $user->id at $reporttime");

    // Check if already queued
    $conditions = array(
            'courseid'=>$courseid,
            'groupid'=>$groupid,
            'userid'=>$user->id,
            'reporttype'=>MANAGERREPORT,
            'reporttime'=>$reporttime
        );
    if ($DB->record_exists('block_istart_reports_queue', $conditions)) {
        return "iStart manager report not queued because the record exists";
    }

    $data = new stdClass();
    $data->courseid = $courseid;
    $data->groupid = $groupid;
    $data->userid = $user->id;
    $data->reporttype = MANAGERREPORT;
    $data->reporttime = $reporttime;

    $DB->insert_record('block_istart_reports_queue', $data);
}

/**
 * Processes manager reports for all intake groups
 * @return true
 */
function process_manager_reports() {
    return true;
}

/**
 * Check if the block is used for istart
 * @return bool whether group is valid for istart
 */
function is_group_valid($group) {
    $date = date_parse($group->idnumber);
    if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"])) {
        // Valid group
        return true;
    } else { 
        //Invalid group
        return false;
    }
}

/**
 * Get whether an istart group has a report due on a date
 * @param int starttime
 * @param int reporttime
 * @param int How many istart weeks in the programme
 * @return bool
 */
function is_istart_report_date($starttime, $reporttime, $totalistartweeks = 24) {
    $startdate = getdate($starttime);
    $reportdate = getdate($reporttime);
    error_log(" - Start day of the week: ".$startdate["wday"]);

    error_log(" - Given date day of the week: ".$reportdate["wday"]);

    // A report is only due on the same day of the week as the istart start date
    if ($startdate["wday"] != $reportdate["wday"]) {
        // No report due
        error_log(" - No report: date is not the same day of the week");
        return false;
    }

    // A report is only due on weeks following an istart week (7 days to 168 days)
    if ( (($reporttime - $starttime) < (7 * WEEKSECS)) or
            (( $reporttime - $starttime) > ( ($totalistartweeks + 1) * 7 * WEEKSECS) ) ) {
        error_log(" - No report: date is not within the istart programme");
        return false;
    }


    return true;
}

/**
 * Get the block id for this block
 * @return int Block ID
 */
function get_blockid($name) {
    global $DB;
    
    if ($block = $DB->get_record('block', array('name'=>$name))) {
        return $block->id;
    } else {
        // this block can't be found in the database
        throw new moodle_exception('noblockid', 'block_istart_reports');
    }
}

/**
 * Gets all courses that contain this block
 * @return array courses[]
 */
function get_courses_with_block($blockid) {
    $searchcriteria = array('blocklist' => $blockid);

    return coursecat::search_courses($searchcriteria);
}

/**
 * Sends an istart user a manager report for a given date.
 * @param int $courseid course ID of the istart course.
 * @param int $groupid ID of the user's group.
 * @param stdClass $user user being reported on.
 * @param string $reportdate <p>The report date as a date/time string.</p>
 * @return true or error
 */
function istart_send_manager_report($courseid, $groupid, $user, $reporttime) {
    global $CFG;

    $reportdate = date("j M Y", $reporttime);

    // Does the user have a manager's email address set?
    profile_load_data($user);
    if ($user->profile_field_manageremailaddress == NULL) {
        return 'Manager email is not set for user: $user->id ($user->firstname $user->lastname).';
    }
    
    $manageremailaddress = $user->profile_field_manageremailaddress;

    // Is the manager's email address valid?
    if (!validate_email($manageremailaddress)) {
        return 'Manager email ($manageremailaddress) not valid for user:'
                . ' $user->id ($user->firstname $user->lastname).';
    }
    error_log(" - Manager's email address: $manageremailaddress");

    // Is there a manager report for the user to send on the given report date?
        // If no: return
    

    // Has the report for that user been sent already?
        // If yes, return

    // Get a list of all course sections for the report week that have reportable completion tasks
        // For each course section in the list:
        // 1. Get the name of the section
        // 2. Get the total number of reportable tasks
        // 3. Get the number of reportable tasks the user has completed.

    // Create the email to send
    $email = new stdClass();

    // Create the email subject "iStart24 Online [Week #] completion report for [Firstname] [Lastname]"
    $a = new stdClass();
    $a->week = "Week 1";
    $a->firstname = $user->firstname;
    $a->lastname = $user->lastname;
    $email->subject = get_string("manageremailsubject", "block_istart_reports", $a);

    // Create the email headers
    $urlinfo = parse_url($CFG->wwwroot);
    $hostname = $urlinfo['host'];

    $email->customheaders = array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "iStart Manager Report" <istart.manager.report@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$courseid,
            'Message-ID: '.istart_report_get_email_message_id($courseid, $groupid, $user->id, $reportdate, $hostname),
            'X-Course-Id: '.$courseid,
     );

    $email->text = manager_report_make_mail_text();
    $email->html = manager_report_make_mail_html();

    // Send the email
    error_log(' > Sending email to: ' . $user->id);

    mtrace('Sending iStart Manager Report Email', '');

    $touser = new object();
    $touser->id = 99999901;
    $touser->email = $manageremailaddress;
    $touser->mailformat = 1;
    $touser->maildisplay = 1;

    $fromuser = new object();
    $fromuser->id = 99999902;
    $fromuser->email = $CFG->supportemail;
    $fromuser->mailformat = 1;
    $fromuser->maildisplay = 1;
    $fromuser->customheaders = $email->customheaders;

    $mailresult = email_to_user($touser, $fromuser, $email->subject,
            $email->text, $email->html);

    if (!$mailresult){
        mtrace("Error: blocks/istart_reports/lib.php istart_send_manager_report(): "
                . "Could not send out email for course $courseid group $groupid "
                . "for report $reportdate to user $user->id"
                . "($manageremailaddress) .. not trying again.");
    } else {
        // TODO: Store that the manager report for the user on the given report date has been sent
        // user->id, groupid, manager's email address, reportdate and email send date.
    }

    return true;
}

/**
 * Create a message-id string to use in the custom headers of report emails
 *
 * message-id is used by email clients to identify emails and to nest conversations
 *
 * @param int $postid The ID of the forum post we are notifying the user about
 * @param int $usertoid The ID of the user being notified
 * @param string $hostname The server's hostname
 * @return string A unique message-id
 */
function istart_report_get_email_message_id($courseid, $groupid, $userid, $reportdate, $hostname) {
    return '<'.hash('sha256','Course: '.$courseid.' Group: '.$groupid.' User: '.$userid.' Report date: '.$reportdate).'@'.$hostname.'>';
}

function manager_report_make_mail_text() {
    // TODO: build text-only version of the email

    // Create the email body
    // Add welcome message
    // For each course section in the list add:
    // 1. The name of the course section
    // 2. The percentage of tasks in that section the user has completed
    // Add email close

    return "text";
}

function manager_report_make_mail_html() {
    // TODO: build HTML version of the email

    // Create the email body
    // Add welcome message
    // For each course section in the list add:
    // 1. The name of the course section
    // 2. The percentage of tasks in that section the user has completed
    // Add email close

    return "<p>HTML text with <b>formatting</b>.</p>";
}
