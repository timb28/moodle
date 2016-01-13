<?php

/**
 * Install trigger for component 'enrol_snipcart'
 *
 * @link https://docs.moodle.org/dev/Installing_and_upgrading_plugin_database_tables#install.php
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_enrol_snipcart_install() {
    global $DB;
    
    // Add Postcode custom user profile field
    $postcode_info_field = new stdClass;
    $postcode_info_field->shortname = 'postcode';
    $postcode_info_field->name = get_string( 'customfieldpostcode', 'enrol_snipcart' );
    $postcode_info_field->datatype = 'text';
    $postcode_info_field->descriptionformat = 1;
    $postcode_info_field->categoryid = 1;
    $postcode_info_field->required = 0;
    $postcode_info_field->locked = 0;
    $postcode_info_field->visible = 1;
    $postcode_info_field->forceunique = 0;
    $postcode_info_field->signup = 0;
    $postcode_info_field->defaultformat = 0;
    
    $DB->insert_record('user_info_field', $postcode_info_field);
    
}