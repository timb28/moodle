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

define('WP_USER_ENDPOINT','wp-json/wp/v2/users/');
define('MAX_USERS_TO_SYNC',20);

/**
 * Handle the \core\event\user_created event.
 *
 * @param object $event The event object.
 */
function user_created(\core\event\user_created $event) {
    $newuser = $event->get_record_snapshot('user', $event->objectid);
    sync_user_to_wordpress($newuser);
    return;
}

/**
 * Synchronises a single user to WordPress
 *
 * @param $user stdClass User to sync
 * @return bool true if sync succeeded
 */
function sync_user_to_wordpress($user, text_progress_trace $trace = null) {
    global $DB, $CFG;

    // Don't sync incomplete users
    if (!$user->email)
        return false;

    // Don't sync deleted and suspended users
    if ($user->deleted || $user->suspended)
        return false;

    // Don't sync temporary accounts with usernames starting 'ac_...
    if (strpos($user->username,'ac_') === 0)
        return false;

    // Don't sync Moodle guest accounts
    if (is_guest(context_course::instance(1), $user))
        return false;

    // Don't sync site admins
    if (is_siteadmin($user->id))
        return false;

    // Check if user exists in WP
    $wpuser = get_wp_user($user);

    if (isset($wpuser) && isset($wpuser->profile["wpuserid"]))
        return $wpuser;

    // Create user in WordPress
    $user->name = $user->firstname. " " . $user->lastname;
    if (isset($trace))
        $trace->output("Synchronising user " . $user->username . " to Wordpress.");
    return create_wp_user($user);
}

/**
 * @param stdClass $user
 * @return false|stdClass User with WP user id
 * @throws dml_exception
 */
function get_wp_user(stdClass $user = null) {

    if (is_null($user))
        return false;

    if( !function_exists("curl_init") &&
        !function_exists("curl_setopt") &&
        !function_exists("curl_exec") &&
        !function_exists("curl_close") ) die ("cURL not installed.");


    $wpurl = get_config('local_wordpresssync', 'wpurl')
        . WP_USER_ENDPOINT;
    $wpusername = get_config('local_wordpresssync', 'wpusername');
    $wppassword = get_config('local_wordpresssync', 'wppassword');

    if (!isset($wpurl) || !isset($wpusername) || !isset($wppassword)) {
        debugging('local_wordpresssync: WordPress settings not yet configured.');
        return false;
    }

    if (!preg_match('|^https://|i', $wpurl)) {
        debugging('local_wordpresssync: WordPress URL must use HTTPS.');
        return false;
    }

    $query['slug']   = $user->username;
    $wpurl.= '?' . http_build_query($query);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wpurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    // REMOVE before production
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    ///////////////////////////
    curl_setopt($ch, CURLOPT_USERPWD, $wpusername . ":" . $wppassword);

    // Execute the request
    $response = curl_exec($ch);

    // Get the HTTP status from the response header.
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if( $httpcode>=200 && $httpcode<300 ) {
        $wpuserarray = json_decode($response);

        if(!isset($wpuserarray) || !is_array($wpuserarray) || count($wpuserarray) == 0)
            return false;

        // Update the Moodle user data with their WordPress User ID
        $wpuser = $wpuserarray[0];

        $user->profile["wpuserid"] = $wpuser->id;
        update_user_profile($user->id,$wpuser->id);
    } else {
        debugging("local_wordpresssync: WordPress error: " . $response);
        return false;
    }

    // close cURL resource, and free up system resources
    curl_close($ch);

    return $user;
}

/**
 * Calls WordPress API to create user
 *
 * @param stdClass $user
 * @return true if token is valid
 * @return false if token is invalid
 */
function create_wp_user($user) {
    global $CFG, $ADMIN;

    // Don't create incomplete users
    if (!$user->email)
        return true;

    if( !function_exists("curl_init") &&
        !function_exists("curl_setopt") &&
        !function_exists("curl_exec") &&
        !function_exists("curl_close") ) die ("cURL not installed.");


    $wpurl = get_config('local_wordpresssync', 'wpurl')
        . WP_USER_ENDPOINT;
    $wpusername = get_config('local_wordpresssync', 'wpusername');
    $wppassword = get_config('local_wordpresssync', 'wppassword');

    if (!isset($wpurl) || !isset($wpusername) || !isset($wppassword)) {
        debugging('WordPress settings not yet configured.');
        return false;
    }

    if (!preg_match('|^https://|i', $wpurl)) {
        debugging('local_wordpresssync: WordPress URL must use HTTPS.');
        return false;
    }

    $post['username']   = $user->username;
    $post['email']      = $user->email;
    $post['password']   = generate_password(); // Use random password
    $post['first_name']  = $user->firstname;
    $post['last_name']   = $user->lastname;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wpurl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    // REMOVE before production
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    ///////////////////////////
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
    curl_setopt($ch, CURLOPT_USERPWD, $wpusername . ":" . $wppassword);



    // Execute the request
    $response = curl_exec($ch);

    // Get the HTTP status from the response header.
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if( $httpcode>=200 && $httpcode<300 ) {
        $newwpuser = json_decode($response);
        if(!isset($newwpuser))
            return false;

        // Update the Moodle user data with their WordPress User ID
        echo "New WP UserID = " . $newwpuser->id;
        $user->profile["wpuserid"] = $newwpuser->id;

        echo "<p>Updating user profile.</p>";
        update_user_profile($user->id,$newwpuser->id);
    } else {
        debugging("local_wordpresssync: Couldn't create WordPress user " . $user->username);
        debugging("local_wordpresssync: WordPress error: " . $response);
    }

    // close cURL resource, and free up system resources
    curl_close($ch);

    return true;
}

/**
 * Get suitable users to synchronise with Wordpress.
 * Excludes suspended, deleted and temporary users.
 *
 * @param int $limitmin
 * @param int $limitmax
 * @return array of Users
 * @throws dml_exception
 */
function get_users_to_sync(int $limitmin = 0, int $limitmax = MAX_USERS_TO_SYNC) {
    global $DB;

    $users = $DB->get_records_sql("SELECT DISTINCT u.*
                                            FROM {user} u
                                            LEFT JOIN {user_info_data} uid on u.id = uid.userid
                                            JOIN {user_info_field} uif ON uid.fieldid = uif.id
                                        WHERE
                                              u.deleted = 0
                                              AND u.suspended = 0
                                              AND LOCATE('ac_', u.username) <> 1
                                              AND u.id NOT IN (
                                                SELECT
                                                  uid2.userid FROM {user_info_data} uid2
                                                  JOIN {user_info_field} uif2 ON uid2.fieldid = uif2.id
                                                WHERE
                                                  uif2.shortname = 'wpuserid'
                                                  AND uid2.data > 0)
                                        ORDER BY u.id DESC
                                        ",null,$limitmin,$limitmax);
    return $users;
}

function update_user_profile($userid, $wpuserid) {
    global $CFG;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    profile_save_data((object)['id' => $userid, 'profile_field_wpuserid' => $wpuserid]);
}
