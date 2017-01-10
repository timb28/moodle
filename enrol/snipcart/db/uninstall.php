<?php

/**
 * Uninstall trigger for component 'enrol_snipcart'
 *
 * @link https://docs.moodle.org/dev/Installing_and_upgrading_plugin_database_tables#uninstall.php
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function xmldb_enrol_snipcart_uninstall() {
    global $DB;
    
    $DB->delete_records('config_plugins', array('plugin' => 'enrol_snipcart'));

}

