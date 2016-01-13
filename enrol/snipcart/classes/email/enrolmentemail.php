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
     * Creates the course list for the html email.
     *
     * @return string the course links
     */
    public function create_html_course_list() {
        global $CFG;
        
        $courselist = '';
        
        foreach ($this->snipcartorder->courses as $course) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new \course_in_list($course);
            
            $courseurl = new \moodle_url('/course/view.php', array('id'=>$course->id));
            
            // get the first course image
            $courseoverviewfiles = $course->get_course_overviewfiles();
            $firstfile = array_shift($courseoverviewfiles);
            
            $isimage = (!empty($firstfile) and $firstfile->is_valid_image());
            
            if ($isimage) {
                $courseimageurl = file_encode_url("$CFG->wwwroot/pluginfile.php",
                        '/'. $firstfile->get_contextid(). '/'. $firstfile->get_component(). '/'.
                        $firstfile->get_filearea(). $firstfile->get_filepath(). $firstfile->get_filename(), true);
            } else {
                $courseimageurl = new \moodle_url('/enrol/snipcart/pix/empty-course-icon.png');
            }
            
            $courselist .= '<!-- BEGIN COLUMNS // -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="templateColumns" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;background-color: #FFFFFF;border-top: 0;border-bottom: 0;">
                                        <tbody><tr>
                                            <td align="left" valign="top" class="columnsContainer" width="25%" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="templateColumn" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: 143px;">
                                                    <tbody><tr>
                                                        <td valign="top" class="leftColumnContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <div style="text-align: right;"><a href="' . $courseurl . '" target="_blank" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #6DC6DD;font-weight: normal;text-decoration: underline;"><img align="none" height="48" src="' . $courseimageurl . '" style="width: 48px;height: 48px;margin: 0px;border: 0;outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;" width="48"></a></div>

                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                
                
            </td>
        </tr>
    </tbody>
</table></td>
                                                    </tr>
                                                </tbody></table>
                                            </td>
                                            <td align="left" valign="top" class="columnsContainer" width="75%" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                                                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="templateColumn" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;width: 443px;">
                                                    <tbody><tr>
                                                        <td valign="top" class="rightColumnContainer" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;"><table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
    <tbody class="mcnTextBlockOuter">
        <tr>
            <td valign="top" class="mcnTextBlockInner" style="mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                
                <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextContentContainer" style="border-collapse: collapse;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;">
                    <tbody><tr>
                        
                        <td valign="top" class="mcnTextContent" style="padding-top: 9px;padding-right: 18px;padding-bottom: 9px;padding-left: 18px;mso-table-lspace: 0pt;mso-table-rspace: 0pt;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #606060;font-family: Helvetica;font-size: 15px;line-height: 150%;text-align: left;">
                        
                            <a href="' . $courseurl . '" target="_blank" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #6DC6DD;font-weight: normal;text-decoration: underline;"><strong>' .$course->fullname . '</strong></a>
                        </td>
                    </tr>
                </tbody></table>
                
            </td>
        </tr>
    </tbody>
</table></td>
                                                    </tr>
                                                </tbody></table>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                    <!-- // END COLUMNS -->';
        }
        
        return $courselist;
    }
    
    /**
     * Creates the content that appears in the text and html emails
     *
     * @return string[] array containing the email variables
     */
    public function create_email_variables() {
        $a = array(
            'subject'=>get_string('email_ordercompletesubject', 'enrol_snipcart'),
            'logourl'=> new \moodle_url('/enrol/snipcart/pix/logo.png'),
            'firstname'=>$this->snipcartorder->user->firstname,
            'heading'=>get_string('email_ordercompleteheading', 'enrol_snipcart'),
            'subheading'=>get_string('email_ordercompletesubheading', 'enrol_snipcart'),
            'invoice'=>get_string('email_ordercompleteinvoice', 'enrol_snipcart'),
            'textcourselinks'=>$this->create_text_course_links(),
            'htmlcourselist'=>$this->create_html_course_list(),
            'copyright'=>get_string('copyright', 'enrol_snipcart'),
            'mailingaddress'=>get_string('email_mailingaddress', 'enrol_snipcart'),
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
        $email->subject         = get_string('email_ordercompletesubject', 'enrol_snipcart');
        
        $a                      = $this->create_email_variables();
        $email->text            = get_string('email_ordercompletetext', 'enrol_snipcart', $a);
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
