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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/coursecatlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/selector/lib.php');

use block_istart_reports\istart_group;
use block_istart_reports\istart_week;
use block_istart_reports\istart_week_report;

define('BLOCK_NAME', 'istart_reports');
define('NUMPASTREPORTDAYS', 6);
define('MANAGERREPORTTYPE', 1);
define('MANAGERROLESHORTNAME', 'coach');
define('COURSEFORMATOPTIONTYPEFORTASKS', 'reportcompletions');

/**
 * Function to be run periodically according to the scheduled task.
 *
 * Finds all the iStart reports that have yet to be mailed out, and mails them
 * out to all managers as well as other maintance tasks.
 *
 */
function istart_reports_cron() {

    core_php_time_limit::raise(300); // terminate if not able to process cron tasks in 5 minutes

    clean_reports();
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

    // Clean store of manager report emails sent and reports processed for past students (sent > 1 year ago)
    $DB->delete_records_select('block_istart_reports', $oldreportconditions);

    // TODO: Clean store of manager report emails sent for past students no longer enrolled
}

/**
 * Queues manager reports for all intake groups for later sending
 * @return true
 */
function process_manager_reports() {
    
    // TODO: Record in the Moodle event log that istart manager reports have started processing

    // Get istart courses that have this block
    $courses = get_courses_with_block(get_blockid(BLOCK_NAME));

    foreach ($courses as $course) {
        $reporttime = time();

        error_log("1. Started processing reports for course: $course->id at time: " . date("Y-m-d", $reporttime)); // TODO remove after testing

        // Only process courses with group mode activated.
        if (groups_get_course_groupmode($course) == NOGROUPS) {
            error_log(" - Course skipped: No groups");
            continue;
        }

        $istart_week_report = new istart_week_report($course, MANAGERREPORTTYPE, $reporttime);
        $istart_week_report->process_manager_reports();
    }

    return true;
}

/**
 * Sends istart manager reports for a given istart intake group
 * @param stdClass $course The istart course object.
 * @param stdClass $group The group to process.
 * @return TODO true if a report was sent
 */
//function process_manager_report_for_group($course, $group) {
//    // Send out all unsent manager reports from the last NUMPASTREPORTDAYS days.
//    // Reports older than NUMPASTREPORTDAYS will not be mailed.  This is to avoid the problem where
//    // cron has not been running for a long time or a student moves iStart group,
//    // and then suddenly people are flooded with mail from the past few weeks or months
//    $daysago = 0;
//
//    if (get_group_istart_week($group)) {
//
//        while ($daysago <= NUMPASTREPORTDAYS) {
//            $reporttime = strtotime(date("Ymd")) - (DAYSECS * $daysago);
//            error_log("2. Started processing group: $group->id ($group->name),  Days ago: $daysago, Report time: $reporttime"); // TODO remove after testing
//            process_manager_report_for_group_on_date($course, $group, $reporttime);
//            $daysago++;
//        }
//    }
//
//    return true;
//}

/**
 * Sends istart manager reports for a given istart intake group
 * @param stdClass $course The istart course.
 * @param stdClass $group the group to be processed.
 * @param string $reporttime The report date as a timestamp.
 * @return TODO true if a report was sent
 */
function process_manager_report_for_group_on_date($course, $group, $reporttime) {
    $starttime = strtotime($group->idnumber);

    // Is there a manager report for the group to send on the given report date?
    if (!is_istart_report_date($starttime,$reporttime)) {
        return false;
    }

    error_log("Started processing reports for group: $group->id ($group->name)"); // TODO: remove after testing

    // Get information on the istart week
    $reportdate = new DateTime();
    $reportdate->setTimestamp($reporttime);
    $istartweek = new \block_istart_reports\istart_week($course->id, get_istart_week_number($group, $reportdate));

    if (!is_array($istartweek)) {
        return false;
    }


    // Send the manager report for every student in that list
    // istart_send_manager_report($courseid, $groupid, $userid, $reportdate);
    $groupmembers = groups_get_members($group->id);

    foreach ($groupmembers as $user) {
        send_manager_report($course, $group, $user, $reporttime, $istartweek);
    }

    return true;
}

/**
 * Sends an istart user a manager report for a given date.
 * @param int $courseid course ID of the istart course.
 * @param int $groupid ID of the user's group.
 * @param stdClass $user user being reported on.
 * @param string $istartweek The istart week.
 * @return true or error
 */
function send_manager_report($course, $group, $user, $reporttime, $istartweek) {
    global $CFG, $DB;

    error_log(" - Sending manager report for $user->id at $reporttime"); // TODO remove after testing

    // Check if already sent
//    try {
//        if ($DB->record_exists_select('block_istart_reports',
//                'courseid = :courseid AND groupid = :groupid AND userid = :userid'
//                . ' AND reporttype = :reporttype AND reporttime = :reporttime AND senttime IS NOT NULL',
//                     array(
//                        'courseid' => $course->id,
//                        'groupid'  => $group->id,
//                        'userid'   => $user->id,
//                        'reporttype' => MANAGERREPORT,
//                        'reporttime' => $reporttime) )) {
////TODO uncomment after testing            return "iStart manager report not sent because it has already been sent";
//        }
//    } catch(Exception $e) {
//        error_log($e, DEBUG_NORMAL);
//        return "iStart manager report not sent because the database cannot be read";
//    }
//
//    // Does the user have a manager's email address set?
//    $manageremailaddress = get_manager_email_address($user);
//    if ($manageremailaddress == NULL) {
//        return 'Manager email is not set for user: $user->id ($user->firstname $user->lastname).';
//    }
//
//    // Is the manager's email address valid?
//    if (!validate_email($manageremailaddress)) {
//        return 'Manager email ($manageremailaddress) not valid for user:'
//                . ' $user->id ($user->firstname $user->lastname).';
//    }

    // Get a list of all course sections for the report week that have reportable completion tasks
//    $tasksections = get_istart_child_task_sections($course->id, $istartweek["sectionid"]);

//    foreach ($tasksections as $sectionid=>$sectionname) {
//        // For each course section in the list:
//        // 1. Get the name of the section
//        // 2. Get the total number of reportable tasks
//        // 3. Get the number of reportable tasks the user has completed.
//
//        $tasksection = array(
//            "sectionname"   => $sectionname,
//            "totaltasks"    => 0, // get_istart_section_total_tasks($course->id, $sectionid),
//            "taskscomplete" => get_istart_tasks_complete($sectionid, $user->id)
//        );
//
//        error_log(" - Task sections: " . $sectionid
//                . ": " . $tasksection['sectionname']
//                . ": " . $tasksection['totaltasks']
//                . ": " . $tasksection['taskscomplete']); // TODO remove after testing
//
//        $tasksections[$sectionid] = $tasksection;
//
//    }

    // Create the email to send
//    $email = new stdClass();
//
//    $reportdate = new DateTime();
//    $reportdate->setTimestamp($reporttime);
//    $istartweeknumber = get_istart_week_number($group, $reportdate);
//    $istartweeklabel = get_istart_week_label($course, $group, $reportdate);
//
//    // Create the email subject "iStart24 Online [Week #] completion report for [Firstname] [Lastname]"
//    $a = new stdClass();
//    $a->istartweeknumber = $istartweeknumber;
//    $a->firstname = $user->firstname;
//    $a->lastname = $user->lastname;
//    $email->subject = get_string("manageremailsubject", "block_istart_reports", $a);
//    unset($a);
//
//    // Create the email headers
//    $urlinfo = parse_url($CFG->wwwroot);
//    $hostname = $urlinfo['host'];
//
//    $email->customheaders = array (  // Headers to make emails easier to track
//            'Return-Path: <>',
//            'List-Id: "iStart Manager Report" <istart.manager.report@'.$hostname.'>',
//            'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$course->id,
//            'Message-ID: '.istart_report_get_email_message_id($course->id, $group->id, $user->id, $reporttime, $hostname),
//            'X-Course-Id: '.$course->id,
//     );
//
//    $email->text = manager_report_make_mail_text($course, $user, $istartweeknumber, $istartweeklabel, $tasksections);
//    $email->html = manager_report_make_mail_html($course, $user, $istartweeknumber, $istartweeklabel, $tasksections);

//    $data = new stdClass();
//    $data->courseid = $course->id;
//    $data->groupid = $group->id;
//    $data->userid = $user->id;
//    $data->reporttype = MANAGERREPORT;
//    $data->reporttime = $reporttime;
//    $data->sentto = $manageremailaddress;
//
//    // Send the email
//    error_log(' > Sending email to: ' . $user->id); // TODO remove after testing
//
//    mtrace('Sending iStart Manager Report Email', '');
//
//    $touser = new object();
//    $touser->id = 99999901;
//    $touser->email = $manageremailaddress;
//    $touser->mailformat = 1;
//    $touser->maildisplay = 1;
//
//    $fromuser = new object();
//    $fromuser->id = 99999902;
//    $fromuser->email = $CFG->supportemail;
//    $fromuser->mailformat = 1;
//    $fromuser->maildisplay = 1;
//    $fromuser->customheaders = $email->customheaders;

//    $mailresult = email_to_user($touser, $fromuser, $email->subject,
//            $email->text, $email->html);
//
//    if (!$mailresult){
//        mtrace("Error: blocks/istart_reports/lib.php istart_send_manager_report(): "
//                . "Could not send out email for course $course->id group $group->id "
//                . "for report $reporttime to user $user->id"
//                . "($manageremailaddress). Error: $mailresult .. not trying again.");
//    } else {
//        // Record the time that the email was sent
//        $data->senttime = time();
//    }


//    // Store that the manager report for the user on the given report date has been sent
//    try {
//        $DB->insert_record('block_istart_reports', $data);
//    } catch(Exception $e) {
//        error_log($e, DEBUG_NORMAL);
//        return "iStart manager report could not be recorded in the database.";
//    }
//
//    return true;
}


/**
 * Check if the block is used for istart
 * @return bool whether group is valid for istart
 */
/*function is_group_valid($group) {
    $date = date_parse($group->idnumber);
    if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"])) {
        // Valid group
        return true;
    } else {
        //Invalid group
        return false;
    }
}*/

/**
 * Get whether an istart group has a report due on a date
 * @param int $starttime of istart intake (usually the 1st of a month)
 * @param int $reporttime of the day the report is created / sent
 * @param int $totalistartweeks how many istart weeks in the programme
 * @return bool true if a report is due on the given $reporttime day
 */
/*function is_istart_report_date($starttime, $reporttime, $totalistartweeks = 24) {
    $startdate = getdate($starttime);
    $reportdate = getdate($reporttime);
    error_log(" - Start day of the week: ".$startdate["wday"]); // TODO remove after testing

    error_log(" - Given date day of the week: ".$reportdate["wday"]); // TODO remove after testing

    // A report is only due on the same day of the week as the istart start date
    if ($startdate["wday"] != $reportdate["wday"]) {
        // No report due
        error_log(" - No report: date is not the same day of the week"); // TODO remove after testing
        return false;
    }


    // A report is only due on weeks following an istart week (7 days to 175 days)
    $reportday = ($reporttime - $starttime) / DAYSECS;
    if ( $reportday < 7 or $reportday > ($totalistartweeks + 1) * 7) {
        error_log(" - No report: date is not within the istart programme"); // TODO remove after testing
        return false;
    }

    return true;
}*/

/**
 * Get the block id for this block
 * @return int Block ID
 */
function get_blockid($name) {
    global $DB;
    
    $block = $DB->get_record('block', array('name'=>$name));
    if (isset($block)) {
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
 * Gets manager's email address for the given user
 * @param stdClass $user The user object
 * @return string manager's email address
 */
function get_manager_email_address($user) {
    profile_load_data($user);
    return clean_text($user->profile_field_manageremailaddress);
}

/**
 * Gets manager for the given user
 * @param stdClass $user The user object
 * @return array manager user objects
 */
function get_manager_users($user) {
    global $DB;

    $managerusers = null;

    $context = context_user::instance($user->id);
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);

    if (!isset($roleid)) {
        return false;
    }

    $userfields = 'u.id, u.username, u.email, u.mailformat, ' . get_all_user_name_fields(true, 'u');
    $roleusers = get_role_users($roleid, $context, false, $userfields);
    if (!empty($roleusers)) {
        $strroleusers = array();
        foreach ($roleusers as $user) {
            $managerusers[] = $user;
        }
    }

    return $managerusers;
}

/**
 * Gets manager for the given user
 * @param stdClass $user The user object
 * @return array manager user objects
 */
function get_manager_user_ids($userid) {
    global $DB;

    $managerusers = null;
    $usercontext = context_user::instance($userid);
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);

    if (!isset($roleid)) {
        return false;
    }

    $roleusers = get_role_users($roleid, $usercontext, false, 'u.id');
    if (!empty($roleusers)) {
        foreach ($roleusers as $user) {
            $managerusers[] = $user->id;
        }
    }

    $manageruserids = '0';
    if (isset($managerusers)) {
        $manageruserids = implode(",", $managerusers);
    }

    return $manageruserids;
}

/**
 * Sets manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function set_manager($user, $managerid) {
    global $DB;
    
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($user->id, MUST_EXIST);

    // Unassign the old managers
    $existingmanagers = get_manager_users($user);
    if (isset($existingmanagers)) {
        foreach ($existingmanagers as $existingmanager) {
            $unassign = role_unassign($roleid, $existingmanager->id, $context->id);
        }
    }

    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_assign($roleid, $managerid, $context->id);
    }
    return isset($success);
}

/**
 * Adds manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function add_manager($userid, $managerid) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($userid, MUST_EXIST);
    
    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_assign($roleid, $managerid, $context->id);
    }

    $event = \block_istart_reports\event\manager_added::create(array(
        'context' => context_user::instance($userid),
        'objectid' => $userid,
        'relateduserid' => $managerid,
    ));
    $event->trigger();

    return isset($success);
}

/**
 * Removes a manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function remove_manager($userid, $managerid) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($userid, MUST_EXIST);

    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_unassign($roleid, $managerid, $context->id);
    }
    return isset($success);
}

/**
 * Gets istart week number
 * @param stdClass $course The course object
 * @param stdClass $group The goup object
 * @param string $date The date to calculate the week from
 * @return int The istart week number
 */
//function get_istart_week_number($group, $atdate) {
//
//    // Get week number of istart week for the week before the report date
//    $istartstart = date_create($group->idnumber);
//
//    // Get days difference between istart start date and the date given
//    $diff = $istartstart->diff($atdate);
//
//    // If the days difference is divisible exactly by 7 then it's should be considered next week
//    if ( ($diff->days % 7) == 0) {
//        $istartweeknumber = ceil($diff->days / 7) + 1;
//    } else {
//        $istartweeknumber = ceil($diff->days / 7);
//    }
//
//    return $istartweeknumber;
//}

/**
 * Gets istart week label
 * @param stdClass $course The course object
 * @param stdClass $group The group object
 * @param string $date The date to calculate the week from
 * @return array The number and name of the istart week
 */
//function get_istart_week_label($course, $group, $atdate) {
//    global $DB;
//
//    $istartweeknumber = get_istart_week_number($group, $atdate);
//
//    // Get the week name from the course section
//
//    try {
//
//        $sql = '
//                SELECT
//                    cs.name
//                FROM
//                    {course_sections} AS cs
//                        JOIN
//                    {course_format_options} AS cfo ON cs.id = cfo.sectionid
//                WHERE
//                    course = :courseid AND cfo.name = "istartweek"
//                        AND cfo.value = :weeknum';
//        $params = array(
//                        'courseid' => $course->id,
//                        'weeknum'  => $istartweeknumber);
//        $weeklabel = $DB->get_field_sql($sql, $params);
//
//    } catch(Exception $e) {
//        error_log($e, DEBUG_NORMAL);
//        return("iStart manager report not sent because the iStart week label cannot be read from the database.");
//    }
//
//    return $weeklabel;
//}

/**
 * Gets istart week section
 * @param stdClass $course The course object
 * @param stdClass $group The group object
 * @param string $date The date to calculate the week from
 * @return array The istart week course section
 */
/*function get_istart_week($courseid, $istartweeknumber) {
    // // REPLACED WITH CLASS istart_week
    global $DB;

    // Get the course section

    try {

        $sql = '
                SELECT
                    cs.name, cs.section
                FROM
                    {course_sections} AS cs
                        JOIN
                    {course_format_options} AS cfo ON cs.id = cfo.sectionid
                WHERE
                    cs.course = :courseid
                        AND cs.visible = 1
                        AND cfo.name = "istartweek"
                        AND cfo.value = :weeknum';
        $params = array(
                        'courseid' => $courseid,
                        'weeknum'  => $istartweeknumber);
        $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

    } catch(Exception $e) {
        error_log($e, DEBUG_NORMAL);
        return("iStart manager report not sent because the iStart week section cannot be read from the database.");
    }

    $weeksection = array(
                        "name"=>$record->name,
                        "sectionid"=>$record->section,
                        "weeknumber"=>$istartweeknumber
                      );

    return $weeksection;
}*/

/**
 * Gets istart section ids and labels for all child sections flagged as containing
 * istart tasks
 * @param int $courseid The course id of the istart course
 * @param int $parentsectionid The id of the parent course section
 * @return array An associative array
 */
/*function get_istart_child_task_sections($courseid, $parentsectionid) {
    global $DB;

    // Calculate the section id of the istart week


    // Get all course sections that contain tasks
    try {

        $sql = '
                SELECT
                    cfo1.sectionid, cs.name
                FROM
                    mdl_course_format_options AS cfo1
                        INNER JOIN
                    mdl_course_format_options AS cfo2 ON cfo1.sectionid = cfo2.sectionid
                        JOIN
                    mdl_course_sections AS cs ON cfo1.sectionid = cs.id
                WHERE
                    cfo1.courseid = :courseid
                        AND cfo1.name = "parent"
                        AND cfo1.value = :parentsectionid
                        AND cfo2.name = :cfotypename
                        AND cfo2.value = 1;';
        $params = array(
                        'courseid' => $courseid,
                        'parentsectionid'=>$parentsectionid,
                        'cfotypename'  => COURSEFORMATOPTIONTYPEFORTASKS);
        $records = $DB->get_records_sql($sql, $params);

    } catch(Exception $e) {
        error_log($e, DEBUG_NORMAL);
        return("Could not obtain istart task sections because the database could not be read.");
    }
    
    $tasksections = array();

    foreach ($records as $record) {
        $tasksections[$record->sectionid] = $record->name;
    }



    return $tasksections;
}*/

/**
 * Gets the total number of tasks in a section
 * @param int $courseid The course id
 * @param int $sectionid The course section id
 * @return int The total number of tasks
 */
/*function get_istart_section_total_tasks($courseid, $sectionid) {
    global $DB;

    // Get all course sections that contain tasks
    try {

        $table = 'course_modules';
        $conditions = array(
                        'course' => $courseid,
                        'completion' => 1,
                        'section'  => $sectionid);
        $totaltasks = $DB->count_records($table, $conditions);

    } catch(Exception $e) {
        error_log($e, DEBUG_NORMAL);
        return("Could not obtain istart total tasks in a section because the database could not be read.");
    }

    return $totaltasks;
}*/

/**
 * Gets the number of tasks a user has completed in a given section
 * @param int $sectionid The course section id
 * @param int $userid The user is
 * @return int The count of tasks complete
 */
//function get_istart_tasks_complete($sectionid, $userid) {
//    global $DB;
//
//    try {
//
//        $sql = '
//                SELECT
//                    COUNT(cm.id) as "total"
//                FROM
//                    {course_modules} cm
//                        JOIN
//                    {label} l ON l.id = cm.instance
//                        JOIN
//                    {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id
//                WHERE
//                    cmc.userid = :userid AND cm.section = :sectionid';
//        $params = array(
//                        'sectionid' => $sectionid,
//                        'userid'  => $userid);
//        $taskscomplete = $DB->get_record_sql($sql, $params);
//
//    } catch(Exception $e) {
//        error_log($e, DEBUG_NORMAL);
//        return("Could not obtain istart tasks complete because the database could not be read.");
//    }
//
//    return $taskscomplete->total;
//}

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
//function istart_report_get_email_message_id($courseid, $groupid, $userid, $reportdate, $hostname) {
//    return '<'.hash('sha256','Course: '.$courseid.' Group: '.$groupid.' User: '.$userid.' Report date: '.$reportdate).'@'.$hostname.'>';
//}

//function manager_report_make_mail_text($course, $user, $istartweeknumber, $istartweeklabel, $tasksections) {
//    // Create the email body
//    // Add welcome message
//    $a = new stdClass();
//    $a->coursename = $course->fullname;
//    $a->firstname = $user->firstname;
//    $a->lastname = $user->lastname;
//    $a->istartweeknumber = $istartweeknumber;
//    $a->istartweeklabel = $istartweeklabel;
//
//    $email = get_string('managerreporttextheader','block_istart_reports', $a);
//    foreach ($tasksections as $sectionid=>$section) {
//        $percentcomplete = ceil( ($section["taskscomplete"] / $section["totaltasks"]) * 100);
//        $graph = ceil($percentcomplete / 10);
//
//        $a->graph = $graph;
//        $a->sectionname = $section["sectionname"];
//        $a->percentcomplete = $percentcomplete;
//        $email .= get_string('managerreporttextbody','block_istart_reports', $a);
//    }
//    $email .= get_string('managerreporttextfooter','block_istart_reports', $a);
//
//    return $email;
//}
//
//function manager_report_make_mail_html($course, $user, $istartweeknumber, $istartweeklabel, $tasksections) {
//    // Create the email body
//    // Add welcome message
//    $a = new stdClass();
//    $a->coursename = $course->fullname;
//    $a->firstname = $user->firstname;
//    $a->lastname = $user->lastname;
//    $a->istartweeknumber = $istartweeknumber;
//    $a->istartweeklabel = $istartweeklabel;
//
//    $email = get_string('managerreporthtmlheader','block_istart_reports', $a);
//    foreach ($tasksections as $sectionid=>$section) {
//        $percentcomplete = ceil( ($section["taskscomplete"] / $section["totaltasks"]) * 100);
//        $graph = ceil($percentcomplete / 10);
//
//        $a->graph = $graph;
//        $a->sectionname = $section["sectionname"];
//        $a->percentcomplete = $percentcomplete;
//        $email .= get_string('managerreporthtmlbody','block_istart_reports', $a);
//    }
//    $email .= get_string('managerreporthtmlfooter','block_istart_reports', $a);
//
//    return $email;
//}

/**
 * Sets manager's email address for a user
 * @param stdClass $user The user object
 * @param string $emailaddress The manager's email address to save
 * @return bool true if success
 */
function set_manager_email_address($user, $emailaddress) {
    profile_load_data($user);
    if (strlen($emailaddress) == 0) {
        $user->profile_field_manageremailaddress = ""; // Avoids it being saved as "0"
    } else {
        $user->profile_field_manageremailaddress = substr($emailaddress, 0, 255);
    }
    profile_save_data($user);
    return true;
}

/**
 * Gets users who should not appear in the candidate manager list
 * @return array user ids of excluded users
 */
function get_excluded_users() {
        global $DB, $CFG, $USER;

        // Exclude site administrators
        $siteadmins = array();
        foreach (explode(',', $CFG->siteadmins) as $admin) {
            $admin = (int)$admin;
            if ($admin) {
                $siteadmins[] = $admin;
            }
        }

        //Exclude users who cannot login
        $nologinusers = $DB->get_fieldset_select('user', 'id', 'auth = "nologin"');

        //Exclude other users (e.g webservice users)
        //Uses profile_field_excludefromuserlists
        $profilefieldusers = array();
        if ($DB->record_exists_select('user_info_field', 'shortname = "excludefromuserlists"')) {
            try {

                $sql = '
                        SELECT
                            u.id
                        FROM
                            {user} u
                                JOIN
                            {user_info_data} uid ON u.id = uid.userid
                                JOIN
                            {user_info_field} uif ON uid.fieldid = uif.id
                        WHERE
                            uif.shortname = :shortname
                                AND uid.data = 1';
                $params['shortname'] = 'excludefromuserlists';
                $records = $DB->get_records_sql($sql, $params);

                foreach ($records as $record) {
                    $profilefieldusers[] = $record->id;
                }

            } catch(Exception $e) {
                error_log($e, DEBUG_NORMAL);
            }
        }

        // Exclude current managers
        $existingmanagers = array();
        foreach (get_manager_users($USER) as $manager) {
            $existingmanagers[] = $manager->id;
        }


        // Merge the arrays containing the excluded users
        $excludedusers = array_merge($siteadmins, $nologinusers, $profilefieldusers, $existingmanagers);

        return $excludedusers;
    }

/**
 * Description of manager_selector
 *
 * @author timbutler
 */
class manager_candidate_selector extends user_selector_base {


    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Candidate managers
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                WHERE $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $userscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($userscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $userscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('candidatemanagersmatching', 'block_istart_reports', $search);
        } else {
            $groupname = get_string('candidatemanagers', 'block_istart_reports');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'blocks/istart_reports/managers.php';
        return $options;
    }
}

/**
 * Description of manager_selector
 *
 * @author timbutler
 */
class manager_existing_selector extends user_selector_base {
    protected $userid;

    public function __construct($name, $options) {
        $this->userid = $options['userid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate managers
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $manageruserids = get_manager_user_ids($this->userid);

        if ($wherecondition) {
            $wherecondition = "$wherecondition AND id IN ($manageruserids)";
        } else {
            $wherecondition = "id IN ($manageruserids)";
        }

        $sql = " FROM {user} u
                WHERE $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $userscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($userscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $userscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('existingmanagersmatching', 'block_istart_reports', $search);
        } else {
            $groupname = get_string('existingmanagers', 'block_istart_reports');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'blocks/istart_reports/managers.php';
        return $options;
    }
}