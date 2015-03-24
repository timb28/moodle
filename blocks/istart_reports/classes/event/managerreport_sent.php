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
 *      Event logged when a student's manager report is emailed.
 * }
 *
 * @since     Moodle 2014051207.00
 * @copyright Harcourts Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class managerreport_sent extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'block_istart_reports';
    }

    public static function get_name() {
        return get_string('eventmanagerreportsent', 'block_istart_reports');
    }

    public function get_description() {
        return "An iStart manager report for the user with id {$this->userid} "
        . "was emailed to their manager with the id {$this->relateduserid}.";
    }
}
