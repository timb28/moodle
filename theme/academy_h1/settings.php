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
 * Settings for the Academy theme for Australia
 *
 * @package   theme_academy_h1
 * @copyright 2011 Harcourts International Pty Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

// Site Menu
$name = 'theme_academy_h1/sitemenu';
$title = get_string('sitemenu','theme_academy_h1');
$description = get_string('sitemenudesc', 'theme_academy_h1');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$settings->add($setting);

// Foot note setting
$name = 'theme_academy_h1/footnote';
$title = get_string('footnote','theme_academy_h1');
$description = get_string('footnotedesc', 'theme_academy_h1');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$settings->add($setting);

// Harcourts One Protection
$name = 'theme_academy_h1/harcourtsoneprotected';
$title = get_string('harcourtsoneprotection','theme_academy_h1');
$description = get_string('harcourtsoneprotectiondesc', 'theme_academy_h1');
$setting = new admin_setting_configcheckbox($name, $title, $description, 1);
$settings->add($setting);

}