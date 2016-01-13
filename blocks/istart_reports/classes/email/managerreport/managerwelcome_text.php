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
 * Creates the text/plain part of the Manager Welcome email
 *
 * @author timbutler
 */
class managerwelcome_text {
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

            // Create the email body
            // Add welcome message
            $a = new \stdClass();
            $a->firstname = $this->user->firstname;
            $a->lastname = $this->user->lastname;

            $this->email.= get_string('managerwelcometextheader','block_istart_reports', $a);
            
            // Use the same footer as the manager report
            $this->email .= get_string('managerwelcometextfooter','block_istart_reports', $a);
            unset($a);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }
        return true;
    }
    
}
