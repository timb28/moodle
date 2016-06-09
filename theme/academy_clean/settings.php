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
 * Moodle's Clean theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_academy_clean
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    
    $settings->add(
                    new admin_setting_configcheckbox(
                            'theme_academy_clean/completionnotificationsenabled',
                            get_string('completionnotificationsenabled', 'theme_academy_clean'),
                            get_string('completionnotificationsenabled_desc', 'theme_academy_clean'),
                            0)
            );
    
    $settings->add(
                    new admin_setting_configtext(
                            'theme_academy_clean/completionnotificationsstartdate',
                            get_string('completionnotificationsstartdate', 'theme_academy_clean'),
                            get_string('completionnotificationsstartdate_desc', 'theme_academy_clean'),
                            null,
                            PARAM_TEXT,
                            30)
            );

    // Piwik site ID
    $name = 'theme_academy_clean/piwiksiteid';
    $title = get_string('piwiksiteid', 'theme_academy_clean');
    $description = get_string('piwiksiteiddesc', 'theme_academy_clean');
    $setting = new admin_setting_configtext($name, $title, $description, 0, PARAM_INT, 2);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
  
    // Invert Navbar to dark background.
    $name = 'theme_academy_clean/invert';
    $title = get_string('invert', 'theme_academy_clean');
    $description = get_string('invertdesc', 'theme_academy_clean');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Custom CSS file.
    $name = 'theme_academy_clean/customcss';
    $title = get_string('customcss', 'theme_academy_clean');
    $description = get_string('customcssdesc', 'theme_academy_clean');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // Footnote setting.
    $name = 'theme_academy_clean/footnote';
    $title = get_string('footnote', 'theme_academy_clean');
    $description = get_string('footnotedesc', 'theme_academy_clean');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
