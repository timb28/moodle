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
 * Scheduled task for processing wordpress synchronisation.
 *
 * @package     local_wordpresssync
 * @copyright   2020 Harcourts International Pty Ltd <academy@harcourts.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wordpresssync\task;

defined('MOODLE_INTERNAL') || die;

/**
 * Simple task to run sync Moodle user accounts with WordPress.
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wordpress_sync_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'local_wordpresssync');
    }

    /**
     * Find Moodle users and synchronise them with WordPress.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/wordpresssync/locallib.php');

        if (empty(get_config('local_wordpresssync', 'wpurl')))
            return false;

        $maxusers = get_config('local_wordpresssync', 'wpmaxusers');
        if (empty($maxusers))
            $maxusers = 1;

        $trace = new \text_progress_trace();
        $trace->output('Starting WordPress user synchronisation...');

        $users = get_users_to_sync(0, $maxusers);
        $synccount = 0;
        foreach ($users as $user) {
            if (sync_user_to_wordpress($user, $trace))
                $synccount++;
        }

        $trace->output($synccount . ' Moodle users synchronised.');
        $trace->finished();
        return true;
    }

}
