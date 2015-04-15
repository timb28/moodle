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
 * Creates the email headers and subject for the Manager Welcome email
 *
 * @author timbutler
 */
class managerwelcome {

    private $course,
            $user;


    /**
     * Constructs the managerreport.
     *
     * @param stdClass $course The iStart course
     * @param stdClass $istartweek The iStart week
     * @param stdClass $group The course group
     * @param stdClass $user The user
     * @param int $reporttime The time (day) the report was created
     */
    public function __construct($course, $user) {
        $this->course       = $course;
        $this->user         = $user;
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
        $user       = $this->user;

        return array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "iStart Manager Welcome" <istart.manager.welcome@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$course->id,
            'Message-ID: <'.hash('sha256','Course: '.$course->id
                    .' User: '.$user->id.' Date: '.now()).'@'.$hostname.'>',
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

        $a = new \stdClass();
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        return get_string("managerwelcomeemailsubject", "block_istart_reports", $a);
    }
}
