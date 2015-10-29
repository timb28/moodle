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
$courseid   = required_param('cid', PARAM_INT);
$instanceid = required_param('id',  PARAM_INT);

$user = $DB->get_record('user', array('id'=>$userid));
$course = $DB->get_record('course', array('id'=>$courseid));
$instance = $DB->get_record('enrol', array('id'=>$instanceid));

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php include($CFG->dirroot.'/enrol/snipcart/enrol.html'); ?>
    </body>
</html>
