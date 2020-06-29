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
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     local_wordpresssync
 * @copyright   2020 Harcourts International Pty Ltd <academy@harcourts.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Handle the \core\event\user_created event.
 *
 * @param object $event The event object.
 */
function user_created(\core\event\user_created $event) {
    error_log("Moodle User Created:" . print_r($event, true));
    $newuser = $event->get_record_snapshot('user', $event->objectid);
    sync_user_to_wordpress($newuser);
    return;
}

function sync_user_to_wordpress($user) {
    global $DB;

    error_log("Starting sync of new user to Wordpress.");

    $user = $event->get_record_snapshot('user', $event->objectid);

    // Don't sync incomplete users
    if (!$user->email)
        return true;

    /* Create user in Joomla */
    $userinfo['username'] = $user->username;
    $userinfo['password'] = $user->password;
    $userinfo['password2'] = $user->password;

    $userinfo['name'] = $user->firstname. " " . $user->lastname;
    $userinfo['email'] = $user->email;
    $userinfo['firstname'] = $user->firstname;
    $userinfo['lastname'] = $user->lastname;

    $userid = $user->id;
    $usercontext = context_user::instance($userid);
    $context_id = $usercontext->id;

    /* Custom fields */
    $query = "SELECT f.id, d.data 
                    FROM {$CFG->prefix}user_info_field as f, {$CFG->prefix}user_info_data d 
                    WHERE f.id=d.fieldid and userid = ?";

    $params = array ($userid);
    $records =  $DB->get_records_sql($query, $params);

    $i = 0;
    $userinfo['custom_fields'] = array ();
    foreach ($records as $field)
    {
        $userinfo['custom_fields'][$i]['id'] = $field->id;
        $userinfo['custom_fields'][$i]['data'] = $field->data;
        $i++;
    }
}
