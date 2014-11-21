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

/**
 * Function to be run periodically according to the scheduled task.
 *
 * Finds all the iStart reports that have yet to be mailed out, and mails them
 * out to all managers as well as other maintance tasks.
 *
 */
function istart_reports_cron() {

    istart_reports_process_manager_reports();


    return true;
}

/**
 * Processes manager reports for all intake groups
 * @return true
 */
function istart_reports_process_manager_reports() {
    
    core_php_time_limit::raise(300); // terminate if not able to process manager reports in 5 minutes

    // Record in the Moodle event log that istart manager reports have started processing
    
    // Clean store of manager report emails sent and reports processed for past students (sent > six months ago)
    // Clean store of manager report emails sent for past students no longer enrolled

    // Get all current istart intakes as an array containing the $group->idnumber for each intake

    // Send out all unsent manager reports from the last six days.
    // Reports older than 6 days will not be mailed.  This is to avoid the problem where
    // cron has not been running for a long time or a student moves iStart group,
    // and then suddenly people are flooded with mail from the past few weeks or months

    // For each istart intake, get whether there there was a report from each day up to 6 days ago
        // If yes, was the report processed?
            // If yes, task complete: continue
            // If no, istart_process_manager_report($intake, $reportdate)
    
    return true;
}

/**
 * Sends istart manager reports for a given istart intake group
 * @param int $groupid ID of the group.
 * @param string $reportdate <p>The report date as a date/time string.</p>
 * @return true
 */
function istart_process_manager_report(int $groupid, string $reportdate) {
    // Is there a manager report for the group to send on the given report date?
        // If no: return

    // Get a list of all users in the group who:
    //  - are students
    //  - have a manager email (user profile field 'manageremail')
    //  - have finished an iStart week recently (after start date + 6 and before start date + 174)

    // Send the manager report for every student in that list
    // istart_send_manager_report($courseid, $groupid, $userid, $reportdate) {

    // Store that the manager report for the group on the given report date has been processed

    // Record in the Moodle event log that istart manager reports have been sent
    // for the groupid and report date

    return true;
}

/**
 * Sends an istart user a manager report for a given date
 * @param int $userid ID of the user.
 * @param int $groupid ID of the user's group.
 * @param string $reportdate <p>The report date as a date/time string.</p>
 * @return true
 */
function istart_send_manager_report($courseid, $groupid, $userid, $reportdate) {
    global $CFG;

    // Does the user have a manager's email address set?
        // If no: return

    // Is there a manager report for the user to send on the given report date?
        // If no: return
    $manageremailaddress = 'tim.butler@harcourts.net';

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
    $a->firstname = "Firstname";
    $a->lastname = "Lastname";
    $email->subject = get_string("manageremailsubject", "block_istart_reports", $a);

    // Create the email headers
    $urlinfo = parse_url($CFG->wwwroot);
    $hostname = $urlinfo['host'];

    $email->customheaders = array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "iStart Manager Report" <istart.manager.report@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$courseid,
            'Message-ID: '.istart_report_get_email_message_id($courseid, $groupid, $userid, $reportdate, $hostname),
            'X-Course-Id: '.$courseid,
     );

    $email->text = manager_report_make_mail_text();
    $email->html = manager_report_make_mail_html();

    // Send the email
    error_log('sending email');

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
        mtrace("Error: blocks/istart_reports/lib.php istart_send_manager_report(): Could not send out email for course $courseid group $groupid for report $reportdate to user $userid".
             " ($manageremailaddress) .. not trying again.");
    } else {
        // TODO: Store that the manager report for the user on the given report date has been sent
        // userid, groupid, manager's email address, reportdate and email send date.
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
