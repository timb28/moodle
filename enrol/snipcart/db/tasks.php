<?php
/**
 * Definition of Snipcart enrolment scheduled tasks.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'enrol_snipcart\task\cron_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
); // 1am every day
