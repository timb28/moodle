<?php

/**
 * A Snipcart order
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace enrol_snipcart;

require_once($CFG->libdir . '/filelib.php'); // curl
require_once("lib.php");
require_once("classes/snipcartaccounts.php");

class snipcartorder {
    
    public  $courses,
            $enrolments,
            $items,
            $order,
            $ordertoken,
            $plugin,
            $status,
            $user,
            $isvalid = false;
    
    /**
     * Constructs the snipcart order
     *
     * @param string[] $neworder with the student and their courses
     */
    public function __construct($neworder) {
        global $DB;
        
        
        // Call the Snipcart API to get the order using the provided order token
        $this->order        = $this->retrieve_order($neworder);
        
        $this->items        = $this->order['items'];
        $this->ordertoken   = $this->order['token'];
        $this->plugin       = enrol_get_plugin('snipcart');
        $this->status       = $this->order['status'];
        
        // Get the user and enrolment from the order item: {userid}-{enrolid}
        foreach ($this->items as $item) {
            $itemid = explode("-", $item['id']);
            
            // The user is the same for every item
            if (empty($this->user)) {
                if (!($this->user = $DB->get_record("user", array("id"=>$itemid[0])))) {
                    $this->plugin->message_error_to_admin("Not a valid user id", print_r($this->order, true));
                    header('HTTP/1.1 400 BAD REQUEST');
                    die;
                }
            }
            
            if (! $enrol = $DB->get_record('enrol', array('id'=>$itemid[1], 'status'=>0)) ) {
                $this->plugin->message_error_to_admin("Not a valid enrol id", print_r($this->order, true));
                header('HTTP/1.1 400 BAD REQUEST');
                die;
            }
            
            $this->enrolments[] = $enrol;
            $this->courses[]    = $DB->get_record('course', array('id'=>$enrol->courseid));
        }
    }
    
    /**
     * Get a Snipcart order using the order
     *
     * @param array $ordertoretrieve
     *
     * @return string[] - array containing the validated order
     */
    public function retrieve_order($ordertoretrieve) {
        global $DB;
        
// todo: remove after local debugging:
        $this->isvalid = true; return $ordertoretrieve['content'];
        
        $itemid = explode("-", $ordertoretrieve['content']['items'][0]['id']);
        
        $enrol = $DB->get_record('enrol', array('id'=>$itemid[1]));
        
        $currency = $enrol->currency;
        $token = $ordertoretrieve['content']['token'];
        
        $manager = get_snipcartaccounts_manager();
        $privateapikey = $manager->get_snipcartaccount_info($currency, 'privateapikey');
        
        // Open a connection back to Snipcart to validate the data
        $c = new \curl();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Basic ' . base64_encode($privateapikey . ':');
        $c->setHeader($headers);
        $result = $c->get("https://app.snipcart.com/api/orders/{$token}");
        
        $snipcartorder =  json_decode($result, true);
        
        if (is_null($snipcartorder) or !isset($snipcartorder['status'])) {
            // this order can't be found on Snipcart
            header('HTTP/1.1 400 BAD REQUEST');
            throw new moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$token, 'currency'=>$currency));
        }
        
        $this->isvalid = true;
        
        return $snipcartorder;
    }
    
    /**
     * Get user information from the order
     *
     * @param string $field
     *
     * @return string containing the value of the field
     */
    public function get_user_field($field) {
        return $this->order['user'][$field];
    }
    
    
//    /**
//     * Enrols the student in the course(s) they purchased
//     *
//     * @return bool true if successful
//     */
//    public function enrol_user() {
//        global $DB;
//        
//        /// get the user and course records
//        
//        foreach ($this->items as $item) {
//            $itemid = explode("-", $item['id']);
//            
//            if (! $course = $DB->get_record("course", array("id"=>$itemid[1]))) {
//                $this->plugin->message_error_to_admin("Not a valid course id", print_r($this->order, true));
//                header('HTTP/1.1 400 BAD REQUEST');
//                die;
//            }
//
//            if (! $context = context_course::instance($course->id, IGNORE_MISSING)) {
//                $this->plugin->message_error_to_admin("Not a valid context id", print_r($this->order, true));
//                header('HTTP/1.1 400 BAD REQUEST');
//                die;
//            }
//
//            if (! $plugin_instance = $DB->get_record("enrol", array("id"=>$itemid[2], "status"=>0))) {
//                $this->plugin->message_error_to_admin("Not a valid instance id", print_r($this->order, true));
//                header('HTTP/1.1 400 BAD REQUEST');
//                die;
//            }
//            
//            if ($plugin_instance->enrolperiod) {
//                $timestart = time();
//                $timeend   = $timestart + $plugin_instance->enrolperiod;
//            } else {
//                $timestart = 0;
//                $timeend   = 0;
//            }
//
//            // Log the purchase of the course enrolment
//            $context = \context_course::instance($course->id);
//            $event = \enrol_snipcart\event\snipcartorder_completed::create(array(
//                'context' => $context,
//                'userid' => $user->id,
//                'courseid' => $course->id,
//                'objectid' => $plugin_instance->id,
//                'other' => $orderitem['token'],
//            ));
//            $event->trigger();
//
//            // Enrol the student in each of the course they have purchased
//            $this->plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);
//            
//        }
//        
//        return true;
//
//    }
}
