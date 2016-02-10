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
            $totalprice,
            $taxes,
            $user,
            $isvalid = false;
    
    /**
     * Constructs the snipcart order
     *
     * @param string $ordertoken of the order
     * @param string $currency of the order
     */
    public function __construct($ordertoken, $currency) {
        global $DB;
        
        // Call the Snipcart API to get the order using the provided order token
        $this->order        = $this->retrieve_order($ordertoken, $currency);
        $this->currency     = $currency;
        
        $this->items        = $this->create_items();
        $this->ordertoken   = $this->order['token'];
        $this->plugin       = enrol_get_plugin('snipcart');
        $this->taxes        = $this->order['taxes'];
        $this->totalprice   = $this->order['finalGrandTotal'];
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
     * Create extra item details
     *
     * @return string[] array of items with extra information
     */
    private function create_items() {
        global $DB;
        
        $items = '';
        
        foreach ($this->order['items'] as $item) {
            
            $itemid = explode("-", $item['id']);
        
            if (! $enrol = $DB->get_record('enrol', array('id'=>$itemid[1])) ) {
                $this->plugin->message_error_to_admin("Not a valid enrol id", print_r($this->order, true));
                header('HTTP/1.1 400 BAD REQUEST');
                die;
            }
        
            $item['courselink'] = new \moodle_url('/course/view.php', array('id' => $enrol->courseid));
            
            $items[] = $item;
        }
        
        return $items;
    }
    
    /**
     * Get a Snipcart order using the order
     *
     * @param array $ordertoretrieve
     *
     * @return string[] - array containing the validated order
     */
    private function retrieve_order($ordertoken, $currency) {
        
        $manager = \enrol_snipcart\get_snipcartaccounts_manager();
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
            $this->plugin->message_error_to_admin("Snipcart couldn't validate order:", print_r($this->order, true));
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
