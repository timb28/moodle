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
 * Settings for the Academy theme for South Africa
 *
 * @package   theme_academy_za
 * @copyright 2011 Harcourts International Pty Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// Site Menu
$name = 'theme_academy_za/sitemenu';
$title = get_string('sitemenu','theme_academy_za');
$description = get_string('sitemenudesc', 'theme_academy_za');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);
 
// Foot note setting
$name = 'theme_academy_za/footnote';
$title = get_string('footnote','theme_academy_za');
$description = get_string('footnotedesc', 'theme_academy_za');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$settings->add($setting);

// Custom CSS file
$name = 'theme_academy_za/customcss';
$title = get_string('customcss','theme_academy_za');
$description = get_string('customcssdesc', 'theme_academy_za');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

}