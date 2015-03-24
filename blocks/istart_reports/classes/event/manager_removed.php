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

namespace block_istart_reports\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The manager_removed event class.
 *
 * @property-read array $other {
 *      Event logged when a student's manager is unassigned.
 * }
 *
 * @since     Moodle 2014051207.00
 * @copyright Harcourts Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class manager_removed extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'd'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'role_assignments';
    }

    public static function get_name() {
        return get_string('eventmanagerremoved', 'block_istart_reports');
    }

    public function get_description() {
        return "The user with id {$this->userid} removed their manager {$this->relateduserid}.";
    }

    public function get_url() {
        return new \moodle_url("/admin/roles/assign.php",
                array('contextid' => $this->contextinstanceid,
                    'userid' => $this->userid,
                    'courseid' => '1'));
    }
}
