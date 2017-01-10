<?php

/**
 * Snipcart shopping cart block Version Details
 * 
 * Payment card icons copyright Minh Do https://www.iconfinder.com/minhm2m
 * Licensed under Creative Commons (Attribution 3.0 Unported)
 *
 * @package   block_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015111800;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2014051200;        // Requires this Moodle version
$plugin->component = 'block_snipcart';  // Full name of the plugin (used for diagnostics)
$plugin->dependencies = array(
    'enrol_snipcart' => ANY_VERSION,    // The Snipcart enrolment module must be present (any version).
);
