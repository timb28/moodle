<?php

/**
 * Called by Snipcart to validate product prices
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');

global $DB;

require_once("lib.php");

$plugin = enrol_get_plugin('snipcart');

$userid     = required_param('uid', PARAM_INT);
$enrolid    = required_param('eid',  PARAM_INT);

$user = $DB->get_record('user', array('id'=>$userid));
$enrol = $DB->get_record('enrol', array('id'=>$enrolid));
$course = $DB->get_record('course', array('id'=>$enrol->courseid));

header('Content-type: application/json');
echo json_encode($plugin->get_add_to_cart_button($user, $course, $enrol, true));
