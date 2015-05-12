<?php
/**
 * The manager_removed event class.
 *
 * @property-read array $other {
 *      Event logged when a student's manager report is emailed.
 * }
 *
 * @since     Moodle 2014051207.00
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace block_istart_reports\event;

defined('MOODLE_INTERNAL') || die();

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
