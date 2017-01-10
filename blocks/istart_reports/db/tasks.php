<?php
/**
 * Definition of iStart Reports block scheduled tasks.
 *
 * @package    block_istart_reports\tasks
 * @author     Tim Butler
 * @copyright  2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'block_istart_reports\task\cron_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '1',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
); // 1am every day
