<?php
/**
 * The snipcartorder_cancelled event class.
 *
 * @property-read array $other {
 *      Event logged when a student cancels the purchase of
 *      one or more courses through Snipcart.
 * }
 *
 * @since     Moodle 2014051207.00
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace enrol_snipcart\event;

defined('MOODLE_INTERNAL') || die();

class snipcartorder_cancelled extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'd'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user_enrolments';
    }

    public static function get_name() {
        return get_string('eventsnipcartordercancelled', 'enrol_snipcart');
    }

    public function get_description() {
        return "The user with id {$this->userid} "
        . "cancelled their purchase of course enrolment {$this->objectid} "
        . "in the order {$this->other}.";
    }
    
    public function get_url() {
        return new \moodle_url('/enrol/users.php', array('id' => $this->courseid));
    }
}
