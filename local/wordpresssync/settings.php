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
* Plugin administration pages are defined here.
*
 * @package     local_wordpresssync
 * @copyright   2020 Harcourts International Pty Ltd <academy@harcourts.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && is_siteadmin($USER)) { // Needs this condition or there is error on login page.
    global $CFG;
    require_once($CFG->dirroot . '/local/wordpresssync/locallib.php');

    $settings = new admin_settingpage('wpsyncsettings', new lang_string('pluginname', 'local_wordpresssync'));

    $settings->add(new admin_setting_configtext(
    'local_wordpresssync/wpurl',
    get_string('settings_url', 'local_wordpresssync'),
    get_string('settings_urldesc', 'local_wordpresssync'), //desc
    '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_wordpresssync/wpusername',
        get_string('settings_username', 'local_wordpresssync'),
        '',
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_wordpresssync/wppassword',
        get_string('settings_password', 'local_wordpresssync'),
        '',
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_wordpresssync/wpmaxusers',
        get_string('settings_maxusers', 'local_wordpresssync'),
        '',
        10,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'local_wordpresssync/tempemaildomain',
        get_string('settings_tempemaildomain', 'local_wordpresssync'),
        '',
        '',
        PARAM_TEXT
    ));

    $ADMIN->add('localplugins', $settings);

}

//// In every plugin there is one if condition added please check it.
//$settings = new admin_settingpage('wpsyncsettings', new lang_string('pluginname', 'local_wordpresssync'));
//$ADMIN->add('localplugins', $settings);


//$existing_services = eb_get_existing_services();
//
//
////$name, $visiblename, $description, $defaultsetting, $choices
//$settings->add(new admin_setting_configselect(
//    "local_edwiserbridge/ebexistingserviceselect",
//    new lang_string('existing_serice_lbl', 'local_edwiserbridge'),
//    get_string('existing_service_desc', 'local_edwiserbridge'),
//    '',
//    $existing_services
//));
//
//
//
//$settings->add(new admin_setting_configtext(
//    'local_edwiserbridge/ebnewserviceinp',
//    get_string('new_service_inp_lbl', 'local_edwiserbridge'),
//    get_string('auth_user_desc', 'local_edwiserbridge'), //desc
//    '',
//    PARAM_RAW
//));
//
//
//$admin_users = eb_get_administrators();
//
//
////$name, $visiblename, $description, $defaultsetting, $choices
//$settings->add(new admin_setting_configselect(
//    "local_edwiserbridge/ebnewserviceuserselect",
//    new lang_string('new_serivce_user_lbl', 'local_edwiserbridge'),
//    '', //new lang_string('web_service_id', 'local_edwiserbridge'),
//    '',
//    $admin_users
//));

