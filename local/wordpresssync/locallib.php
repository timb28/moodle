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
define('MAX_USERS_TO_SYNC',10);

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
    if ($user->deleted || $user->suspended || $user->auth != 'manual')
        return false;

    // Don't sync temporary accounts with usernames starting 'ac_...
    if (strpos($user->username,'ac_') === 0)
        return false;

    // Check if user exists in WP
    $wpuser = get_wp_user($user);

    if (isset($wpuser) && isset($wpuser->id)) {
        // Update the Moodle user data with their WordPress User ID
        debugging("Found existing WordPress user with idential username: " . $wpuser->id);
        $user->profile["wpuserid"] = $wpuser->id;

        debugging("Updating Moodle user profile.");
        update_user_profile($user->id,$wpuser->id);

        return false;
    }

    // Create user in WordPress
    $user->name = $user->firstname. " " . $user->lastname;
    if (isset($trace))
        $trace->output("Synchronising user " . $user->username . " to Wordpress.");
    return create_wp_user($user);
}

/**
 * Create a Moodle User object, with WordPress ID, if one exists in WordPress.
 *
 * @param stdClass $user
 * @return false|stdClass User with WP user id or false if user doesn't exist
 * @throws dml_exception
 */
function get_wp_user(stdClass $user = null) {
    global $CFG;

    if (is_null($user))
        return false;

    if( !function_exists("curl_init") &&
        !function_exists("curl_setopt") &&
        !function_exists("curl_exec") &&
        !function_exists("curl_close") ) die ("cURL not installed.");


    $wpurl = get_config('local_wordpresssync', 'wpurl');
    if (empty($wpurl))
        return false;

    $wpurl.= WP_USER_ENDPOINT;
    $wpusername = get_config('local_wordpresssync', 'wpusername');
    $wppassword = get_config('local_wordpresssync', 'wppassword');

    if (empty($wpusername) || empty($wppassword)) {
        debugging('local_wordpresssync: WordPress settings not yet configured.');
        return false;
    }

    if (!preg_match('|^https://|i', $wpurl)) {
        debugging('local_wordpresssync: WordPress URL must use HTTPS.');
        return false;
    }

    $query['search']   = $user->username;
    $query['context']  = 'edit'; // required to receive the WordPress username

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wpurl . "?"
        . http_build_query($query, null, '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    if ($CFG->debugdeveloper) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    curl_setopt($ch, CURLOPT_USERPWD, $wpusername . ":" . $wppassword);

    // Execute the request
    $response = curl_exec($ch);

    // Get the HTTP status from the response header.
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if( $httpcode>=200 && $httpcode<300 ) {
        $wpuserarray = json_decode($response);

        if(!isset($wpuserarray) || !is_array($wpuserarray) || count($wpuserarray) == 0)
            return false;

        // WP API User search returns an array of users who match.
        foreach ($wpuserarray as $wpuser) {
            // Find the WP user in the array that has the identical username.
            if ($wpuser->username == $user->username) {
                return $wpuser;
            }
        }

        return false;
    } else {
        debugging("local_wordpresssync: WordPress error: " . $response);
        return false;
    }

    // close cURL resource, and free up system resources
    curl_close($ch);

    return $user;
}

/**
 * Checks if a WordPress exists with the given email address
 *
 * @param string $emailaddress
 * @return false|string False if no user exists with the email or the username if one does
 * @throws dml_exception
 */
function get_wp_user_by_email(string $emailaddress = null) {
    global $CFG;

    if (is_null($emailaddress))
        return false;

    if( !function_exists("curl_init") &&
        !function_exists("curl_setopt") &&
        !function_exists("curl_exec") &&
        !function_exists("curl_close") ) die ("cURL not installed.");


    $wpurl = get_config('local_wordpresssync', 'wpurl');
    if (empty($wpurl))
        return false;

    $wpurl.= WP_USER_ENDPOINT;
    $wpusername = get_config('local_wordpresssync', 'wpusername');
    $wppassword = get_config('local_wordpresssync', 'wppassword');

    if (empty($wpusername) || empty($wppassword)) {
        debugging('local_wordpresssync: WordPress settings not yet configured.');
        return false;
    }

    if (!preg_match('|^https://|i', $wpurl)) {
        debugging('local_wordpresssync: WordPress URL must use HTTPS.');
        return false;
    }

    $query['search']   = $emailaddress;
    $query['context']  = 'edit'; // required to receive the WordPress username

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wpurl . "?"
        . http_build_query($query, null, '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    if ($CFG->debugdeveloper) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    curl_setopt($ch, CURLOPT_USERPWD, $wpusername . ":" . $wppassword);

    // Execute the request
    $response = curl_exec($ch);

    // Get the HTTP status from the response header.
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if( $httpcode>=200 && $httpcode<300 ) {
        $wpuserarray = json_decode($response);

        if(!isset($wpuserarray) || !is_array($wpuserarray) || count($wpuserarray) == 0)
            return false;

        $wpuser = $wpuserarray[0];
        return $wpuser->username;
    } else {
        debugging("local_wordpresssync: WordPress error: " . $response);
        return false;
    }

    // close cURL resource, and free up system resources
    curl_close($ch);

    return $user;
}

/**
 * Creates a new WordPress user by calling the WordPress API.
 * The following Moodle user details are sent:
 * - username
 * - email address
 * - first name
 * - last name
 * - full name
 *
 * Note: The WordPress user is created with a random password
 *       that doesn't match their Moodle account
 *
 * @param stdClass $user Moodle User to create in WordPress
 * @return true if user was created
 * @return false if user was not created
 * @throws dml_exception
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


    $wpurl = get_config('local_wordpresssync', 'wpurl');
    if (empty($wpurl))
        return false;

    $wpurl.= WP_USER_ENDPOINT;
    $wpusername = get_config('local_wordpresssync', 'wpusername');
    $wppassword = get_config('local_wordpresssync', 'wppassword');
    $tempemaildomain = str_replace('@','',
                            get_config('local_wordpresssync', 'tempemaildomain')); // @ added later


    if (empty($wpusername) || empty($wppassword)) {
        debugging('local_wordpresssync: WordPress settings not yet configured.');
        return false;
    }

    if (!preg_match('|^https://|i', $wpurl)) {
        debugging('local_wordpresssync: WordPress URL must use HTTPS.');
        return false;
    }

    if (!isset($tempemaildomain)) {
        debugging('local_wordpresssync: A temporary email address domain must be configured.');
        return false;
    }

    $post['username']   = $user->username;
    // Remove comments from email address
    $rawemail = preg_replace('/\([\s\S]+?\)/', '', $user->email);

    // Check if a WordPress user exists with the same email address and different username
    $existingwpuser = get_wp_user_by_email($rawemail);
    if (!empty($existingwpuser)) {
        if ($existingwpuser == $user->username) {
            return false;
        } else {
            $rawemail = uuid() . '@' . $tempemaildomain;
        }
    }

    $post['email']      = $rawemail;
    $post['password']   = generate_password(); // Use random password
    $post['first_name']  = $user->firstname;
    $post['last_name']   = $user->lastname;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $wpurl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
    if ($CFG->debugdeveloper) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
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
        debugging("New WP UserID = " . $newwpuser->id);
        $user->profile["wpuserid"] = $newwpuser->id;

        debugging("Updating user profile.");
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

    return $DB->get_records_sql("SELECT DISTINCT u.*
                                            FROM {user} u
                                            LEFT JOIN {user_info_data} uid on u.id = uid.userid
                                            JOIN {user_info_field} uif ON uid.fieldid = uif.id
                                        WHERE
                                              u.deleted = 0
                                              AND u.suspended = 0
                                              AND LOCATE('ac_', u.username) <> 1
                                              AND u.auth = 'manual'
                                              AND u.id > 2
                                              AND u.id NOT IN (
                                                SELECT
                                                  uid2.userid FROM {user_info_data} uid2
                                                  JOIN {user_info_field} uif2 ON uid2.fieldid = uif2.id
                                                WHERE
                                                  uif2.shortname = 'wpuserid'
                                                  AND uid2.data > 0)
                                        ORDER BY u.id ASC
                                        ",null,$limitmin,$limitmax);
}

/**
 * Saves the WordPress user ID in the Moodle user profile for the given user.
 *
 * @param int $userid ID of user to update
 * @param int $wpuserid WordPress user ID
 */
function update_user_profile(int $userid, int $wpuserid) {
    global $CFG;

    require_once($CFG->dirroot.'/user/profile/lib.php');

    profile_save_data((object)['id' => $userid, 'profile_field_wpuserid' => $wpuserid]);
}

/**
 * Generates VALID RFC 4211 COMPLIANT Universally Unique Identifiers (UUID) version 4.
 * See https://www.php.net/manual/en/function.uniqid.php#94959
 *
 * @return string UUID v4
 *
 */
function uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

        // 32 bits for "time_low"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),

        // 16 bits for "time_mid"
        mt_rand(0, 0xffff),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand(0, 0x0fff) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand(0, 0x3fff) | 0x8000,

        // 48 bits for "node"
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
