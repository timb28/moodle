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
 * Plugin installation changes.
 *
 * @package     local_wordpresssync
 * @copyright   2020 Harcourts International Pty Ltd <academy@harcourts.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('FIELD_SHORTNAME','wpuserid');
define('FIELD_NAME','WordPress User ID');
define('FIELD_DATATYPE','text');
define('FIELD_DESCRIPTION','Set when the Moodle user has been synchronised to WordPress');
define('FIELD_CATEGORY','1');
define('FIELD_VISIBLE','0');

function xmldb_local_wordpresssync_install() {
    $wps_install = new local_wordpresssync_install ();
    $wps_install->create_user_profile_field ();

    return true;
}

class local_wordpresssync_install {

    function create_user_profile_field() {
        global $DB;

        error_log("Creating new user profile field");

        // Check if the field exists
        $fieldname = $DB->get_field('user_info_field', 'name', array('shortname' => FIELD_SHORTNAME));

        if (!$fieldname) {
            $newprofilefield = array();
            $newprofilefield['shortname']   = FIELD_SHORTNAME;
            $newprofilefield['name']        = FIELD_NAME;
            $newprofilefield['datatype']    = FIELD_DATATYPE;
            $newprofilefield['description'] = FIELD_DESCRIPTION;
            $newprofilefield['categoryid']  = FIELD_CATEGORY;
            $newprofilefield['visible']     = FIELD_VISIBLE;
            $newprofilefield['id'] = $DB->insert_record('user_info_field', $newprofilefield);
        }
    }
}