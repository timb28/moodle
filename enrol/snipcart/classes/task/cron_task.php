<?php
/**
 * A scheduled task for Snipcart enrolment plugin.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace enrol_snipcart\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'enrol_snipcart');
    }

    /**
     * Run enrol_snipcart cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/enrol/snipcart/lib.php');
        mtrace("Running Snipcart enrolment scheduled tasks");
        $snipcart = new \enrol_snipcart_plugin();
        $snipcart->cron();
    }

}
