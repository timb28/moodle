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
 * Creates the text/html part of the Manager Report email
 *
 * @author timbutler
 */
class managerreport_html {
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
     * Gets the manager HTML email content
     *
     * @return string The contents of the text/html part of the email
     */
    public function get_email_content() {
        return $this->email;
    }

    /**
     * Gets the manager text/html part of the email
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
            $a->coursename          = $this->course->fullname;
            $a->firstname           = $this->user->firstname;
            $a->lastname            = $this->user->lastname;
            $a->istartweeknumber    = $this->istartweek->weeknumber;
            $a->istartweekname      = $this->istartweek->weekname;

            $this->email.= $this->create_email_header($a);

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
                $this->email .= $this->create_email_body($a);
            }
            $this->email .= $this->create_email_footer($a);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }
        return true;
    }

    /**
     * Create the manager email header
     *
     * @return string Contents of the email header
     */
    private function create_email_header($a) {
        global $CFG;
        $title          = get_string('managerreporthtmltitle','block_istart_reports', $a);
        $headerintro    = get_string('managerreporthtmlheaderintro','block_istart_reports', $a);
        $heading        = get_string('managerreporthtmlheading','block_istart_reports', $a);

        include 'managerreport_header.php';
        return $html;
    }

    /**
     * Create the manager email body
     *
     * @return string Contents of the email body
     */
    private function create_email_body($a) {
        global $CFG;
        $tasksummary = get_string('managerreporthtmltasksummary','block_istart_reports', $a);

        include 'managerreport_body.php';
        return $html;
    }

    /**
     * Create the manager email footer
     *
     * @return string Contents of the email footer
     */
    private function create_email_footer($a) {
        global $CFG;
        $istartinfo     = get_string('managerreporthtmlistartinfo','block_istart_reports', $a);
        $watchlabel     = get_string('managerreporthtmlwatchlabel','block_istart_reports', $a);
        $readlabel      = get_string('managerreporthtmlreadlabel','block_istart_reports', $a);
        $connectlabel   = get_string('managerreporthtmlconnectlabel','block_istart_reports', $a);
        $dolabel        = get_string('managerreporthtmldolabel','block_istart_reports', $a);
        $actionlabel    = get_string('managerreporthtmlactionlabel','block_istart_reports', $a);
        $actionurl      = get_string('managerreporthtmlactionurl','block_istart_reports', $a);
        $copyright      = get_string('managerreporthtmlcopyright','block_istart_reports', $a);
        $reason         = get_string('managerreporthtmlreason','block_istart_reports', $a);
        $address        = get_string('managerreporthtmladdress','block_istart_reports', $a);

        include 'managerreport_footer.php';
        return $html;
    }
}
