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
            $currency,
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
    public function __construct($ordertoken, $currency) {
        global $DB;
        
        // Call the Snipcart API to get the order using the provided order token
        $this->order        = $this->retrieve_order($ordertoken, $currency);
        $this->currency     = $currency;
        
        $this->items        = $this->order['items'];
        $this->ordertoken   = $this->order['token'];
        $this->plugin       = enrol_get_plugin('snipcart');
        $this->status       = $this->order['status'];
        
        // Get the user and enrolment from the order item: {userid}-{enrolid}
        foreach ($this->items as $item) {
            $itemid = explode("-", $item['id']);
            
            // The user is the same for every item
            if (empty($this->user)) {
// todo               if (!($this->user = $DB->get_record("user", array("id"=>$itemid[0])))) {
                if (!($this->user = $DB->get_record("user", array("id"=>3862)))) {
                    $this->plugin->message_error_to_admin("Not a valid user id", print_r($this->order, true));
                    header('HTTP/1.1 400 BAD REQUEST');
                    die;
                }
            }
            
// todo           if (! $enrol = $DB->get_record('enrol', array('id'=>$itemid[1], 'status'=>0)) ) {
            if (! $enrol = $DB->get_record('enrol', array('id'=>218, 'status'=>0)) ) {
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
    public function retrieve_order($ordertoken, $currency) {
        
        $manager = get_snipcartaccounts_manager();
        $privateapikey = $manager->get_snipcartaccount_info($currency, 'privateapikey');
        
        // Open a connection back to Snipcart to validate the data
        $c = new \curl();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Basic ' . base64_encode($privateapikey . ':');
        $c->setHeader($headers);
        $result = $c->get("https://app.snipcart.com/api/orders/{$ordertoken}");
        
        $snipcartorder =  json_decode($result, true);
        
        if (is_null($snipcartorder) or !isset($snipcartorder['status'])) {
            // this order can't be found on Snipcart
            header('HTTP/1.1 400 BAD REQUEST');
            throw new \moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$ordertoken, 'currency'=>$currency));
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
    
}
