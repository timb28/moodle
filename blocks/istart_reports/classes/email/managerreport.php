<?php

/**
 * iStart Reports block
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_istart_reports\email;

/**
 * Creates the email headers and subject for the Manager Report email
 *
 * @author timbutler
 */
class managerreport {

    const REPORTTYPE = 1;

    public  $course,
            $istartweek,
            $group,
            $user,
            $reporttime;


    /**
     * Constructs the managerreport.
     *
     * @param stdClass $course The iStart course
     * @param stdClass $istartweek The iStart week
     * @param stdClass $group The course group
     * @param stdClass $user The user
     * @param int $reporttime The time (day) the report was created
     */
    public function __construct($course, $istartweek, $group, $user, $reporttime) {
        $this->course       = $course;
        $this->istartweek   = $istartweek;
        $this->group        = $group;
        $this->user         = $user;
        $this->reporttime   = $reporttime;
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
        return get_string("manageremailsubject", "block_istart_reports", $a);
    }
}
