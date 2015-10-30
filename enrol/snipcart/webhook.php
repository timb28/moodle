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

$body = json_decode($json, true);

if (is_null($body) or !isset($body['eventName'])) {
    // When something goes wrong, return an invalid status code
    // such as 400 BadRequest.
    header('HTTP/1.1 400 Bad Request');
    return;
}

// Todo: Remove logging of all requests
error_log('-----------------');
error_log('New Snipcart call');
error_log(print_r($body, true));
error_log('-----------------');

switch ($body['eventName']) {
    case 'order.completed':
        // This is an order:completed event
        // do what needs to be done here.
        error_log('Snipcart: order complete');
        break;
}

// Return a valid status code such as 200 OK.
header('HTTP/1.1 200 OK');