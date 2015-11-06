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
     * Creates the course links for the text email.
     *
     * @return string the course links
     */
    public function create_text_course_links() {
        $courselinks = '';
        
        foreach ($this->snipcartorder->courses as $course) {
            $courseurl = new \moodle_url('/course/view.php', array('id'=>$course->id));
            
            $courselinks.= "\r\n{$course->fullname} ($courseurl)\r\n";
        }
        
        return $courselinks;
    }
    
    /**
     * Creates the content that appears in the text and html emails
     *
     * @return string[] array containing the email variables
     */
    public function create_email_variables() {
        $a = array(
            'firstname'=>$this->snipcartorder->user->firstname,
            'subject'=>get_string('ordercompleteemailsubject', 'enrol_snipcart'),
            'textcourselinks'=>$this->create_text_course_links(),
        );
        return $a;
    }
    
    /**
     * Creates the html email
     * 
     * @param string[] $a array containing email variables
     *
     * @return string containing the html email
     */
    public function create_html_email($a) {
        $html = '';
        include 'enrolmentemail_html.php';
        return $html;
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
                               . get_string('ordercompleteemailfooter', 'enrol_snipcart');
        
        $a                      = $this->create_email_variables();
        $email->text            = get_string('ordercompleteemailtext', 'enrol_snipcart', $a);
        $email->html            = $this->create_html_email($a);

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
