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
require_once("classes/snipcartaccounts.php");

class snipcartorder {
    
    public  $courses,
            $items,
            $order,
            $ordertoken,
            $user,
            $isvalid = false;
    
    /**
     * Constructs the snipcart order
     *
     * @param string[] $neworder with the student and their courses
     */
    public function __construct($neworder) {
        global $DB;
        
        $this->order        = $this->retrieve_order($neworder);
        $this->ordertoken   = $this->order['token'];
        $this->items        = $this->order['items'];
        
        $firstitemids = explode("-", $this->items[0]['id']);
        
        $this->user =  $DB->get_record('user', array('id'=>$firstitemids[0]));
        
        foreach ($this->items as $item) {
            $itemid = explode("-", $item['id']);
            $this->courses[] = $DB->get_record('course', array('id'=>$itemid[1]));
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
        
        $firstitemids = explode("-", $ordertoretrieve['content']['items'][0]['id']);
        
        $enrolinstance = $DB->get_record('enrol', array('id'=>$firstitemids[2]));
        
        $currency = $enrolinstance->currency;
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
    
}
