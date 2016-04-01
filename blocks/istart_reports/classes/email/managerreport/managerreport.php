<?php

/**
 * iStart Reports block
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_istart_reports\email\managerreport;

/**
 * Creates the email headers and subject for the Manager Report email
 *
 * @author timbutler
 */
class managerreport {

    const REPORTTYPE = 1;

    public  $course,
            $istartweek,
            $istartgroup,
            $group,
            $istartuser,
            $user,
            $reporttime;


    /**
     * Constructs the managerreport.
     *
     * @param stdClass $course The iStart course
     * @param stdClass $istartweek The iStart week
     * @param stdClass $istartgroup The course group
     * @param stdClass $istartuser The user
     * @param stdClass $manager The user's manager
     * @param int $reporttime The time (day) the report was created
     */
    public function __construct($course, $istartgroup, $istartuser, $manager, $reporttime) {
        $this->course       = $course;
        $this->istartgroup  = $istartgroup;
        $this->group        = $istartgroup->group;
        $this->istartweek   = $istartgroup->istartweek;
        $this->istartuser   = $istartuser;
        $this->user         = $istartuser->user;
        $this->manager      = $manager;
        $this->reporttime   = $reporttime;
    }

    /**
     * Sends the manager report email to a single manager
     *
     * @param stdClass $istartgroup The iStart group
     * @param stdClass $istartuser The iStart user
     * @param stdClass $manager The user that is the iStart users' manager
     * @return bool true if successful, false otherwise
     */
    public function send_manager_report_to_manager () {
        global $CFG, $DB, $COURSE;

        $course         = $this->course;
        $istartgroup    = $this->istartgroup;
        $group          = $this->group;
        $user           = $this->user;
        $manager        = $this->manager;

        // Create the email to send
        $email = new \stdClass();

        $reportdate = new \DateTime();
        $reportdate->setTimestamp($this->reporttime);

        $managerreport_text = new managerreport_text($course, $this->istartgroup, $this->istartuser);
        $managerreport_html = new managerreport_html($course, $this->istartgroup, $this->istartuser);

        $email->customheaders   = $this->get_email_headers();
        $email->subject         = $this->get_email_subject();
        $email->text            = $managerreport_text->get_email_content();
        $email->html            = $managerreport_html->get_email_content();

        // Send it from the support email address
        $fromuser = new \stdClass();
        $fromuser->id = 99999902;
        $fromuser->email = $CFG->supportemail;
        $fromuser->mailformat = 1;
        $fromuser->maildisplay = 1;
        $fromuser->customheaders = $email->customheaders;

        // Prepare data for block_istart_report database entry
        $data = new \stdClass();
        $data->courseid     = $course->id;
        $data->groupid      = $group->id;
        $data->userid       = $user->id;
        $data->managerid    = $manager->id;
        $data->reporttype   = managerreport::REPORTTYPE;
        $data->reportweek   = $istartgroup->reportweek;
        $data->reportdate   = $istartgroup->reportdate;
        $data->reporttime   = $this->reporttime;
        $data->sentto       = $manager->email;
        $data->senttime     = 0;

        $mailresult = email_to_user($manager, $fromuser, $email->subject,
        $email->text, $email->html);

        if (!$mailresult){
            error_log("Error: "
                    . "Could not send out email for course $course->id group $group->id "
                    . "for report $this->reporttime about user $user->id"
                    . "to their manager ($manager->email). Error: $mailresult .. not trying again.");
            return false;
        } else {
            // Record the time that the email was sent
            $data->senttime = time();
        }

        // Store that the manager report for the user on the given report date has been sent
        try {
            $DB->insert_record('block_istart_reports', $data);
        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }

        // Log the sending of the manager report
        $context = \context_course::instance($COURSE->id);
        $event = \block_istart_reports\event\managerreport_sent::create(array(
            'context' => $context,
            'objectid' => $user->id,
            'relateduserid' => $manager->id,
        ));
        $event->trigger();

        return true;
    }

    /**
     * Creates the report email headers.
     *
     * @return array The email headers
     */
    public function get_email_headers() {
        global $CFG;

        // Create the email headers
        $urlinfo    = parse_url($CFG->wwwroot);
        $hostname   = $urlinfo['host'];
        $course     = $this->course;
        $group      = $this->group;
        $user       = $this->user;

        return array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "iStart Manager Report" <istart.manager.report@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$course->id,
            'Message-ID: <'.hash('sha256','Course: '.$course->id.' Group: '.$group->id
                    .' User: '.$user->id.' Report date: '.$this->reporttime).'@'.$hostname.'>',
            'X-Course-Id: '.$course->id,
            );
    }

    /**
     * Creates the report email subject.
     *
     * @return string The email subject
     */
    public function get_email_subject() {
        $user = $this->user;
        $istartweek = $this->istartweek;

        $a = new \stdClass();
        $a->istartweeknumber = $istartweek->weeknumber;
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        return get_string("managerreportemailsubject", "block_istart_reports", $a);
    }
}
