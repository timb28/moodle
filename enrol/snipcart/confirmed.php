<?php

/**
 * Adds new instance of enrol_snipcart to specified course
 * or edits current instance.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

if (isguestuser()) {
    redirect(new moodle_url('/', array('redirect' => 0)));
}

$order = required_param('order', PARAM_ALPHANUMEXT);

$plugin = enrol_get_plugin('snipcart');

$validatedorder = $plugin->snipcart_get_order($order);

$userid = $USER->id;  // Owner of the page
$context = context_system::instance();

// todo: record event in the Moodle event log

$PAGE->set_url('/enrol/snipcart/confirmed.php', array('order'=>$order));
$PAGE->set_pagelayout('mydashboard');
$PAGE->blocks->add_region('content');
$PAGE->set_cacheable(false);
$PAGE->set_context($context);

switch ($validatedorder['status']) {
    case 'Processed':
        include($CFG->dirroot.'/enrol/snipcart/orderprocessed.php');
        break;
    default:
        die;
}
