<?php

/**
 * Email to notify students they have bought one or more Moodle courses
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace enrol_snipcart\email;

require_once($CFG->libdir . '/weblib.php'); // curl

defined('MOODLE_INTERNAL') || die();

class enrolmentemail {
    
    private $snipcartorder;
    
    /**
     * Constructs the enrolment email.
     *
     * @param stdClass $snipcartorder snipcartorder object
     */
    public function __construct($snipcartorder) {
        $this->snipcartorder= $snipcartorder;
    }
    
    /**
     * Creates the email headers.
     *
     * @return array The email headers
     */
    public function get_email_headers() {
        global $CFG;

        // Create the email headers
        $urlinfo    = parse_url($CFG->wwwroot);
        $hostname   = $urlinfo['host'];

        return array (  // Headers to make emails easier to track
            'Return-Path: <>',
            'List-Id: "Snipcart Order Complete" <enrol_snipcart@'.$hostname.'>',
            'List-Help: '.$CFG->wwwroot,
            'Message-ID: <'.hash('sha256','Order: '.$this->snipcartorder->ordertoken).'@'.$hostname.'>',
            );
    }
    
    /**
     * Creates the course links.
     *
     * @return string the course links
     */
    public function get_course_links() {
        $courselinks = '';
        
        foreach ($this->snipcartorder->courses as $course) {
            $courseurl = new \moodle_url('/course/view.php', array('id'=>$course->id));
            
            $courselinks.= "\r\n\r\n";
            $courselinks.= "{snipcartorder->fullname}:\r\n$courseurl";
        }
        
        return $courselinks;
    }
    
    /**
     * Sends the course enrolment notification email to the student
     *
     * @return bool true if successful, false otherwise
     */
    public function send_enrolment_email() {
        global $CFG, $DB, $COURSE;

        // Create the email to send
        $email = new \stdClass();

        $email->customheaders   = $this->get_email_headers();
        $email->subject         = get_string('ordercompleteemailsubject', 'enrol_snipcart');
        
        $email->text            = get_string('ordercompleteemailheader', 'enrol_snipcart', array('firstname'=>$this->snipcartorder->user->firstname))
                                . $this->get_course_links()
                                . get_string('ordercompleteemailfooter', 'enrol_snipcart');
        
        $email->html            = "todo: <strong>HTML</strong> email";

        // Send it from the support email address
        $fromuser = new \stdClass();
        $fromuser->id = 99999902;
        $fromuser->email = $CFG->supportemail;
        $fromuser->mailformat = 1;
        $fromuser->maildisplay = 1;
        $fromuser->customheaders = $email->customheaders;

        $mailresult = email_to_user($this->snipcartorder->user, $fromuser, $email->subject,
        $email->text, $email->html);

        if (!$mailresult){
            error_log("Error: "
                    . "Could not send out email for order {$this->snipcartorder->ordertoken} "
                    . "to the student ({$this->snipcartorder->user->email}). Error: $mailresult .. not trying again.");
            return false;
        }

        return true;
    }
    
    
}
