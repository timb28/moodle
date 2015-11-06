<?php

/**
 * Listens for payment notification from Snipcart
 *
 * This script waits for payment notification from Snipcart,
 * then double checks that data by sending it back to Snipcart.
 * If Snipcart verifies this then it sets up the enrolment for that
 * user.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use enrol_snipcart\snipcartorder;
use enrol_snipcart\email\enrolmentemail;

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require("../../config.php");
require_once("lib.php");

$json = file_get_contents('php://input');

/// Keep out casual intruders
if (empty($json) or !empty($_GET)) {
    header('HTTP/1.1 400 Bad Request');
    return;
}

$order = json_decode($json, true);

if (is_null($order) or !isset($order['eventName'])) {
    // When something goes wrong, return an invalid status code
    // such as 400 BadRequest.
    header('HTTP/1.1 400 Bad Request');
    return;
}

switch ($order['eventName']) {
    case 'order.completed':
        $plugin = enrol_get_plugin('snipcart');
        
        $ordertoken = $plugin->snipcart_get_ordertoken($order);
        $currency   = $plugin->snipcart_get_ordercurrency($order);
        
        $snipcartorder = new snipcartorder($ordertoken, $currency);
        
        if (!$snipcartorder->isvalid) {
            error_log('Invalid Snipcart order: ' . print_r($body, true));
            header('HTTP/1.1 400 BAD REQUEST');
            throw new moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$ordertoken, 'currency'=>$currency));
        }
        
        foreach ($snipcartorder->enrolments as $enrol) {
            $plugin->snipcart_enrol_user($snipcartorder->user, $enrol, $snipcartorder->ordertoken);
        }
  
        // Notify the user that they are now enrolled in the courses they purchased
        $email = new enrol_snipcart\email\enrolmentemail($snipcartorder);
        $email->send_enrolment_email();

        // Update the user's address, city and postcode if not set in Moodle
        $plugin->snipcart_update_user($snipcartorder);
        
        break;
        
    case 'order.status.changed':
        $plugin = enrol_get_plugin('snipcart');
        
        $ordertoken = $plugin->snipcart_get_ordertoken($order);
        $currency   = $plugin->snipcart_get_ordercurrency($order);
        
        $snipcartorder = new snipcartorder($ordertoken, $currency);
        
        if (!$snipcartorder->isvalid) {
            error_log('Invalid Snipcart order: ' . print_r($body, true));
            header('HTTP/1.1 400 BAD REQUEST');
            die;
        }
        
        // Unenrol the student if the order was cancelled
        if ($snipcartorder->status == 'Cancelled') {
            foreach ($snipcartorder->enrolments as $enrol) {
                $plugin->snipcart_unenrol_user($snipcartorder->user, $enrol, $snipcartorder->ordertoken);
            }
        }
        
        break;
}

// Return a valid status code such as 200 OK.
header('HTTP/1.1 200 OK');