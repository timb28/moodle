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
class managerwelcome_html {
    private $user,
            $manager,
            $email;

    /**
     * Constructs the managerwelcome_text email.
     *
     * @param stdClass $group The group object.
     */
    public function __construct($user, $manager) {
        $this->user     = $user;
        $this->manager  = $manager;

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

            // Create the email body
            // Add welcome message
            $a = new \stdClass();
            $a->firstname           = $this->user->firstname;
            $a->lastname            = $this->user->lastname;

            $this->email.= $this->create_email_header($a);
            $this->email.= $this->create_email_footer($a);

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
        $title          = get_string('managerwelcomeemailsubject','block_istart_reports', $a);
        $headerintro    = get_string('managerwelcomehtmlheaderintro','block_istart_reports', $a);
        $heading        = get_string('managerwelcomehtmlheading','block_istart_reports', $a);
        $content        = get_string('managerwelcomehtmlcontent','block_istart_reports', $a);

        include 'managerwelcome_header.php';
        return $html;
    }

    /**
     * Create the manager welcome email footer
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

        include 'managerwelcome_footer.php';
        return $html;
    }
}
