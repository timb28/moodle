<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace block_istart_reports\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The manager_added event class.
 *
 * @property-read array $other {
 *      Event logged when a student is assigned a manager.
 * }
 *
 * @since     Moodle 2014051207.00
 * @copyright Harcourts Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class manager_added extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'role_assignments';
    }

    public static function get_name() {
        return get_string('eventmanageradded', 'block_istart_reports');
    }

    public function get_description() {
        return "The user with id {$this->userid} added their manager {$this->relateduserid}.";
    }

    public function get_url() {
        return new \moodle_url("/admin/roles/assign.php",
                array('contextid' => $this->contextid,
                    'userid' => $this->userid,
                    'courseid' => '1'));
    }
}
