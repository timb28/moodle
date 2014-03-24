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
 * Harcourts enrolment plugin.
 *
 * @package    enrol_harcourtsone
 * @copyright  2014 Tim Butler {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Harcourts enrolment plugin implementation.
 * @author Tim Butler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_harcourtsone_plugin extends enrol_plugin {

    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        // no real enrolments here!
        return;
    }

    public function unenrol_user(stdClass $instance, $userid) {
        // nothing to do, we never enrol here!
        return;
    }

    public function roles_protected() {
        // Users can't tweak the roles later.
        return true;
    }

    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap can't enrol other users manually manually.
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap can't unenrol other users manually manually.
        return false;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap can't tweak period and status.
        return false;
    }

    public function show_enrolme_link(stdClass $instance) {
         return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    function enrol_page_hook(stdClass $instance) {
        global $OUTPUT;

        $OUTPUT .= get_string("harcourntsonelink", "enrol_harcourts", "http://one.harcourts.com.au/");

        return $OUTPUT;
    }
}
