<?php
/**
 * Snipcart enrolment plugin
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SOCIAL_USERNAME_PREFIX', 'social_user_');

require_once($CFG->libdir . '/filelib.php'); // curl
require_once("classes/event/snipcartorder_completed.php");
require_once("classes/snipcartaccounts.php");

class enrol_snipcart_plugin extends enrol_plugin {

    public function roles_protected() {
        // Users may tweak the roles later.
        return false;
    }
    
    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'snipcart') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/snipcart:config', $context)) {
            $managelink = new moodle_url('/enrol/snipcart/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap may unenrol other users manually.
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap may tweak period and status.
        return true;
    }
    
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/snipcart:config', $context);
    }
    
    /**
     * Is it possible to hide/show enrol instance via standard UI?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/snipcart:config', $context);
    }
    
    /**
     * Is it possible for the user to buy access using this instance?
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_user_access_instance($instance) {
        global $DB, $USER;
        
        $enrol = $DB->get_record('enrol', array('id'=>$instance->id));
        $user = $DB->get_record('user', array('id'=>$USER->id)); // ensure current user info
        
        // Social (public) users are prevented from accessing
        // the course if so configured and this user is a social user.
        if ($enrol->customint1 == ENROL_INSTANCE_DISABLED && 
                stripos($user->username, SOCIAL_USERNAME_PREFIX) === 0) {
            return false;
        }
        
        // Only let users pay for courses in their own currency
        if (stripos($enrol->currency, $user->country) !== 0) {
            return false;
        }
        
        return true;
    }
    
    public function cron() {
        $trace = new text_progress_trace();
        $this->process_expirations($trace);
    }
    
    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'snipcart') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/snipcart:config', $context)) {
            $editlink = new moodle_url("/enrol/snipcart/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                    array('class' => 'iconsmall')));
        }

        return $icons;
    }
    
    public function get_currencies() {
        $codes = array(
            'AUD', 'NZD', 'USD', 'ZAR');
        $currencies = array();
        foreach ($codes as $c) {
            $currencies[$c] = new lang_string($c, 'core_currencies');
        }

        return $currencies;
    }
    
    public function get_localised_currency($currency, $cost) {
        
        global $CFG;
        
        if (empty($currency) or empty($cost)) {
            return;
        }
        
        $manager = get_snipcartaccounts_manager();
        
        $snipcartaccounts = $manager->get_snipcartaccounts();
        
        $symbols = array();
        foreach ($snipcartaccounts as $account) {
            $symbols[$account->currencycode] = $account->currencyformat;
        }
        
        return str_replace('%c', $cost, $symbols[$currency]);
    }
    
    /**
     * Returns localised name of enrol instance
     *
     * @param object $instance (null is accepted too)
     * @return string
     */
    public function get_instance_name($instance) {
        if (empty($instance->name)) {
            $enrol = $this->get_name();
            return get_string('pluginname', 'enrol_'.$enrol);
        } else {
            $context = context_course::instance($instance->courseid);
            return format_string("{$instance->name} {$instance->currency} " . 
                    $this->get_localised_currency($instance->currency, format_float($instance->cost, 2, false)),
                    true, array('context'=>$context));
        }
    }
    
    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/snipcart:config', $context)) {
            return NULL;
        }

        return new moodle_url('/enrol/snipcart/edit.php', array('courseid'=>$courseid));
    }
    
    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/snipcart:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/snipcart:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''), get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }
    
    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    public function enrol_page_hook(stdClass $instance) {
        global $CFG, $USER, $OUTPUT, $DB;
        
        // Notify the admin if a user's country is not set (ignore the guest user)
        if (!($USER->country) and !($USER->id == 1)) {
            $this->message_error_to_admin('A Moodle user cannot purchase a course because their country is not set', $USER);
        }

        ob_start();
        
        if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id))) {
            return ob_get_clean();
        }
        
        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return ob_get_clean();
        }

        if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
            return ob_get_clean();
        }
        
        if (!$this->can_user_access_instance($instance)) {
            return ob_get_clean();
        }
        
        if ( (float) $instance->cost <= 0 ) {
            $cost = (float) $this->get_config('cost');
        } else {
            $cost = (float) $instance->cost;
        }

        if (abs($cost) < 0.01) { // no cost, other enrolment methods (instances) should be used
            echo '<p>'.get_string('nocost', 'enrol_snipcart').'</p>';
        } else {

            $course = $DB->get_record('course', array('id'=>$instance->courseid));
            $user = $USER;
            $plugin = enrol_get_plugin('snipcart');
            $userid = $USER->id;
            $courseid = $course->id;
            $instanceid = $instance->id;
        
            include($CFG->dirroot.'/enrol/snipcart/enrol.html');
        }
        
        return $OUTPUT->box(ob_get_clean());
    }
    
    function message_error_to_admin($subject, $data) {
        $admin = get_admin();
        $site = get_site();

        $message = "$site->fullname:  Snipcart failure.\n\n$subject\n\n";

        $eventdata = new stdClass();
        $eventdata->modulename        = 'moodle';
        $eventdata->component         = 'enrol_snipcart';
        $eventdata->name              = 'snipcart_enrolment';
        $eventdata->userfrom          = $admin;
        $eventdata->userto            = $admin;
        $eventdata->subject           = "Snipcart Error: ".$subject;
        $eventdata->fullmessage       = $message . print_r($data, true);
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml   = '';
        $eventdata->smallmessage      = '';
        message_send($eventdata);
    }
    
    /**
     * Restore instance and map settings.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $course
     * @param int $oldid
     */
    public function restore_instance(restore_enrolments_structure_step $step, stdClass $data, $course, $oldid) {
        global $DB;
        if ($step->get_task()->get_target() == backup::TARGET_NEW_COURSE) {
            $merge = false;
        } else {
            $merge = array(
                'courseid'   => $data->courseid,
                'enrol'      => $this->get_name(),
                'roleid'     => $data->roleid,
                'cost'       => $data->cost,
                'currency'   => $data->currency,
            );
        }
        if ($merge and $instances = $DB->get_records('enrol', $merge, 'id')) {
            $instance = reset($instances);
            $instanceid = $instance->id;
        } else {
            $instanceid = $this->add_instance($course, (array)$data);
        }
        $step->set_mapping('enrol', $oldid, $instanceid);
    }
    
    /**
     * Restore user enrolment.
     *
     * @param restore_enrolments_structure_step $step
     * @param stdClass $data
     * @param stdClass $instance
     * @param int $oldinstancestatus
     * @param int $userid
     */
    public function restore_user_enrolment(restore_enrolments_structure_step $step, $data, $instance, $userid, $oldinstancestatus) {
        $this->enrol_user($instance, $userid, null, $data->timestart, $data->timeend, $data->status);
    }
    
    /**
     * Student's can self-enrol buy paying for the course
     *
     * @param stdClass $instance course enrol instance
     *
     * @return bool - true means show "Enrol me in this course" link in course UI
     */
    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }
    
    /**
     * Get a Snipcart order using the order
     *
     * @param array $order
     *
     * @return string[] - array containing the validated order
     */
    public function snipcart_get_order($order) {
        
        $instance = $this->snipcart_get_instance_from_itemid($order['content']['items'][0]['id']);
        $currency = $instance->currency;
        $token = $order['content']['token'];
        
        return $this->snipcart_get_order_from_token_and_currency($token, $currency);;
    }
    
    /**
     * Get a Snipcart order using the order token and currency by calling their API
     *
     * @param string $token of the order
     * @param string $currency of the order
     *
     * @return string[] - array containing the validated order
     */
    public function snipcart_get_order_from_token_and_currency($token, $currency) {
        
        $manager = get_snipcartaccounts_manager();
        $privateapikey = $manager->get_snipcartaccount_info($currency, 'privateapikey');
        
        // Open a connection back to Snipcart to validate the data
        $c = new curl();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Basic ' . base64_encode($privateapikey . ':');
        $c->setHeader($headers);
        $result = $c->get("https://app.snipcart.com/api/orders/{$token}");
        
        $snipcartorder =  json_decode($result, true);
        
        if (is_null($snipcartorder) or !isset($snipcartorder['status'])) {
            // this order can't be found on Snipcart
            header('HTTP/1.1 400 BAD REQUEST');
            throw new moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$token, 'currency'=>$currency));
            die;
        }
        
        return $snipcartorder;
    }
    
    /**
     * Gets the Moodle course instance for a given Snipcart order
     *
     * @param string $itemid
     *
     * @return stdClass Moodle course
     */
    public function snipcart_get_course_from_itemid($itemid) {
        global $DB;
        
        $ids = explode("-", $itemid);
        
        return $DB->get_record('course', array('id'=>$ids[1]));
    }
    
    /**
     * Gets the Snipcart enrolment instance for a given Snipcart order
     *
     * @param string $itemid
     *
     * @return stdClass Moodle user
     */
    public function snipcart_get_instance_from_itemid($itemid) {
        global $DB;
        
        $ids = explode("-", $itemid);
        
        return $DB->get_record('enrol', array('id'=>$ids[2]));
    }
    
    /**
     * Gets the Moodle user for a given Snipcart order
     *
     * @param string $itemid
     *
     * @return stdClass Moodle user
     */
    public function snipcart_get_user_from_itemid($itemid) {
        global $DB;
        
        $ids = explode("-", $itemid);
        
        return $DB->get_record('user', array('id'=>$ids[0]));
    }
    
    /**
     * Updates the Moodle user account with new information from Snipcart order
     *
     * @param string[] $validatedorder
     *
     * @return bool true if user updated, false otherwise
     */
    public function snipcart_update_user($validatedorder) {
        global $DB;
        
        $ids = explode("-", $validatedorder['items'][0]['id']);
        
        $user = $DB->get_record('user', array('id'=>$ids[0]));
        
        if (empty($user)) {
            return false;
        }
        
        $userupdated = false;
        
        if (empty($user->city) and $validatedorder['user']['billingAddressCity'] != 'null') {
            $user->city = $validatedorder['user']['billingAddressCity'];
            $userupdated = true;
        }
        
        if (empty($user->address) and $validatedorder['user']['billingAddressAddress1'] != 'null') {
            $user->address = $validatedorder['user']['billingAddressAddress1'] . 
                    ', ' . $validatedorder['user']['billingAddressAddress2'];
            $userupdated = true;
        }
        
        $postcodefieldid = $DB->get_field('user_info_field', 'id', array( 'shortname' => 'postcode'));
        $postcodefield = $DB->get_record('user_info_data', array('userid' => $user->id, 'fieldid' => $postcodefieldid));
        
        if (empty($postcodefield->data) and $validatedorder['user']['billingAddressPostalCode'] != 'null' ) {
            $customfield = new stdClass;
            $customfield->userid = $user->id;
            $customfield->fieldid = $postcodefieldid;
            $customfield->data = $validatedorder['user']['billingAddressPostalCode'];

            if ( !$DB->record_exists( 'user_info_data', array( 'userid' => $user->id, 'fieldid' => $postcodefieldid ) ) ) {
                $DB->insert_record('user_info_data', $customfield);
            } else {
                $record = $DB->get_record( 'user_info_data', array( 'userid' => $user->id, 'fieldid' => $postcodefieldid ) );
                $customfield->id = $record->id;
                $DB->update_record('user_info_data', $customfield);
            }
        }
        
        if (empty($user->phone1) and $validatedorder['user']['billingAddressPhone'] != 'null') {
            $user->phone1 = $validatedorder['user']['billingAddressPhone'];
            $userupdated = true;
        }
        
        if ($userupdated) {
            $DB->update_record('user', $user);
            return true;
        }
        
        return false;
    }
    
    /**
     * Enrols student in the course they purchased
     *
     * @param string[] $orderitem
     *
     * @return stdClass Moodle course
     */
    public function snipcart_enrol_user($orderitem) {
        global $DB;
        
        $oids = explode("-", $orderitem['id']);
        
        /// get the user and course records

        if (! $user = $DB->get_record("user", array("id"=>$oids[0]))) {
            $this->message_error_to_admin("Not a valid user id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $course = $DB->get_record("course", array("id"=>$oids[1]))) {
            $this->message_error_to_admin("Not a valid course id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $context = context_course::instance($course->id, IGNORE_MISSING)) {
            $this->message_error_to_admin("Not a valid context id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $plugin_instance = $DB->get_record("enrol", array("id"=>$oids[2], "status"=>0))) {
            $this->message_error_to_admin("Not a valid instance id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }
        
        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }
        
        // Log the purchase of the course enrolment
        $context = \context_course::instance($course->id);
        $event = \enrol_snipcart\event\snipcartorder_completed::create(array(
            'context' => $context,
            'userid' => $user->id,
            'courseid' => $course->id,
            'objectid' => $plugin_instance->id,
            'other' => $orderitem['token'],
        ));
        $event->trigger();

        
        // Todo: Email the student a link to get started
                
        // Enrol the student in each of the course they have purchased
        return $this->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);
    }
    
    /**
     * Unenrols student in the course they purchased when the order is cancelled
     *
     * @param string[] $orderitem
     *
     * @return stdClass Moodle course
     */
    public function snipcart_unenrol_user($orderitem) {
        global $DB;
        
        $oids = explode("-", $orderitem['id']);
        
        /// get the user and course records

        if (! $user = $DB->get_record("user", array("id"=>$oids[0]))) {
            $this->message_error_to_admin("Not a valid user id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $course = $DB->get_record("course", array("id"=>$oids[1]))) {
            $this->message_error_to_admin("Not a valid course id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $context = context_course::instance($course->id, IGNORE_MISSING)) {
            $this->message_error_to_admin("Not a valid context id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }

        if (! $plugin_instance = $DB->get_record("enrol", array("id"=>$oids[2], "status"=>0))) {
            $this->message_error_to_admin("Not a valid instance id", $orderitem);
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }
        
        // Log the order cancellation
        $context = \context_course::instance($course->id);
        $event = \enrol_snipcart\event\snipcartorder_cancelled::create(array(
            'context' => $context,
            'userid' => $user->id,
            'courseid' => $course->id,
            'objectid' => $plugin_instance->id,
            'other' => $orderitem['token'],
        ));
        $event->trigger();

        // Unenrol the student in each of the courses they purchased
        return $this->unenrol_user($plugin_instance, $user->id);
    }

}

