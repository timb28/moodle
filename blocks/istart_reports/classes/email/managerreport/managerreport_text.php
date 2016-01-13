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
 * Creates the text/plain part of the Manager Report email
 *
 * @author timbutler
 */
class managerreport_text {
    private $course,
            $istartweek,
            $tasksections,
            $istartuser,
            $user,
            $email;

    /**
     * Constructs the managerreport_text email.
     *
     * @param stdClass $group The group object.
     */
    public function __construct($course, $istartgroup, $istartuser) {
        $this->course      = $course;
        $this->istartweek   = $istartgroup->istartweek;
        $this->tasksections = $this->istartweek->tasksections;
        $this->istartuser   = $istartuser;
        $this->user         = $istartuser->user;

        $this->create_email();
    }

    /**
     * Gets the manager text email content
     *
     * @return string The contents of the text/plain part of the email
     */
    public function get_email_content() {
        return $this->email;
    }

    /**
     * Gets the manager text/plain part of the email
     *
     * @return bool True if successful, false otherwise
     */
    private function create_email() {
        try {
            if (!isset($this->tasksections)) {
                return false;
            }

            // Create the email body
            // Add welcome message
            $a = new \stdClass();
            $a->coursename = $this->course->fullname;
            $a->firstname = $this->user->firstname;
            $a->lastname = $this->user->lastname;
            $a->istartweeknumber = $this->istartweek->weeknumber;
            $a->istartweekname = $this->istartweek->weekname;

            $this->email.= get_string('managerreporttextheader','block_istart_reports', $a);
            foreach ($this->tasksections as $tasksection) {
                $numtasks = $tasksection->numtasks;
                $numtaskscomplete = $this->istartuser->get_num_tasks_complete($tasksection->sectionid);

                $percentcomplete = 0;
                if ($numtasks > 0) {
                    $percentcomplete = ceil( ($numtaskscomplete / $numtasks) * 100);
                }
                $graph = floor($percentcomplete / 5);

                $a->graph = $graph;
                $a->sectionname = $tasksection->sectionname;
                $a->percentcomplete = $percentcomplete;
                $this->email .= get_string('managerreporttextbody','block_istart_reports', $a);
            }
            $this->email .= get_string('managerreporttextfooter','block_istart_reports', $a);
            unset($a);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }
        return true;
    }
    
}
