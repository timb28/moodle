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

    private $user,
            $manager;


    /**
     * Constructs the managerreport.
     *
     * @param stdClass $user The user
     * @param stdClass $manager The manager user
     */
    public function __construct($user, $manager) {
        global $DB;

        $this->user     = $user;

        // Get the full USER object for manager
        $this->manager  = $DB->get_record('user', array('id' => $manager->id));
    }

    /**
     * Sends the manager welcome email to a single manager
     *
     * @param stdClass $istartgroup The iStart group
     * @param stdClass $istartuser The iStart user
     * @param stdClass $manager The user that is the iStart users' manager
     * @return bool true if successful, false otherwise
     */
    public function send_manager_welcome_to_manager () {
        global $CFG, $DB;

        if (empty($this->user) || empty($this->manager)) {
            return false;
        }

        $user           = $this->user;
        $manager        = $this->manager;

        // Create the email to send
        $email = new \stdClass();

        $managerwelcome_text = new managerwelcome_text($user, $manager);
        $managerwelcome_html = new managerwelcome_html($user, $manager);

        $email->customheaders   = $this->get_email_headers();
        $email->subject         = $this->get_email_subject();
        $email->text            = $managerwelcome_text->get_email_content();
        $email->html            = $managerwelcome_html->get_email_content();

        // Send it from the support email address
        $fromuser = new \stdClass();
        $fromuser->id = 99999902;
        $fromuser->email = $CFG->supportemail;
        $fromuser->mailformat = 1;
        $fromuser->maildisplay = 1;
        $fromuser->customheaders = $email->customheaders;

        $mailresult = email_to_user($manager, $fromuser, $email->subject,
        $email->text, $email->html);

        if (!$mailresult){
            error_log("Error: "
                    . "Could not send out manager welcome email for user "
                    . "$manager->id ($manager->email) becoming the manager of "
                    . "user $user->id. Error: $mailresult .. not trying again.");
            return false;
        }

        return true;
    }

    /**
     * Creates the welcome email headers.
     *
     * @return array The email headers
     */
    public function get_email_headers() {
        global $CFG;

        // Create the email headers
        $urlinfo    = parse_url($CFG->wwwroot);
        $hostname   = $urlinfo['host'];
        $user       = $this->user;
        $manager    = $this->manager;

        return array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "iStart Manager Welcome" <istart.manager.welcome@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot.'/user/profile.php?id='.$user->id,
            'Message-ID: <'.hash('sha256','Manager: '.$manager->id
                    .' User: '.$user->id.' Date: '.strftime('%F %T')).'@'.$hostname.'>',
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
