<?php
/**
 * iStart Reports block version details
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2014112003;
$plugin->component = 'block_istart_reports';
$plugin->release = '0.1 (Build: 2014112003)';
$plugin->requires = 2014051200;                     // Requires Moodle 2.7+
$plugin->maturity = MATURITY_ALPHA;
$plugin->dependencies = array(
    'format_flexsections' => 2014090400,   // The Flexsections course format must be present.
);
