<?php
/**
 * A scheduled task for block_istart_reports.
 *
 * Sends out:
 *             - Weekly manager report
 *
 * @package    block_istart_reports
 * @author     Tim Butler
 * @copyright  2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_istart_reports\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'block_istart_reports');
    }

    /**
     * Run istart_reports cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/blocks/istart_reports/lib.php');
        mtrace("Running iStart Reports scheduled tasks");
        istart_reports_cron();
    }

}
