<?php


// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for the Academy theme
 *
 * @package   theme_academy
 * @copyright 2010 Caroline Kennedy of Synergy Learning
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

/*
// Block region width
$name = 'theme_academy/regionwidth';
$title = get_string('regionwidth','theme_academy');
$description = get_string('regionwidthdesc', 'theme_academy');
$default = 240;
$choices = array(200=>'200px', 240=>'240px', 290=>'290px', 350=>'350px', 420=>'420px');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$temp->add($setting); */

// Site Menu
$name = 'theme_academy/sitemenu';
$title = get_string('sitemenu','theme_academy');
$description = get_string('sitemenudesc', 'theme_academy');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);
 
// Foot note setting
$name = 'theme_academy/footnote';
$title = get_string('footnote','theme_academy');
$description = get_string('footnotedesc', 'theme_academy');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$settings->add($setting);

// Custom CSS file
$name = 'theme_academy/customcss';
$title = get_string('customcss','theme_academy');
$description = get_string('customcssdesc', 'theme_academy');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

// H1 Authentication Script
/*$name = 'theme_academy/harcourtsonescript';
$title = get_string('harcourtsonescript','theme_academy');
$description = get_string('harcourtsonescriptdesc', 'theme_academy');
$setting = new admin_setting_configtext($name, $title, $description, 'http://one.harcourts.com.au/e-cademy/apps/moodleauth.aspx');
$settings->add($setting);*/

// H1 Token Validation Script
/*$name = 'theme_academy/harcourtsonetokenvalidatorscript';
$title = get_string('h1tokenvalidatorscript','theme_academy');
$description = get_string('h1tokenvalidatorscriptdesc', 'theme_academy');
$setting = new admin_setting_configtext($name, $title, $description, 'http://one.harcourts.com.au/e-cademy/apps/validateh1token.aspx');
$settings->add($setting);*/

// Harcourts One URL
$name = 'theme_academy/harcourtsoneurl';
$title = get_string('harcourtsoneurl','theme_academy');
$description = get_string('harcourtsoneurldesc', 'theme_academy');
$setting = new admin_setting_configtext($name, $title, $description, 'http://one.harcourts.com.au');
$settings->add($setting);

// Harcourts One Protection
$name = 'theme_academy/harcourtsoneprotected';
$title = get_string('harcourtsoneprotection','theme_academy');
$description = get_string('harcourtsoneprotectiondesc', 'theme_academy');
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$settings->add($setting);

}