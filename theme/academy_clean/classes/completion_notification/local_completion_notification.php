<?php
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
 * Completion notification functions.
 *
 * @package    local_completionnotification
 * @copyright  2016 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_academy_clean\completion_notification;

defined('MOODLE_INTERNAL') || die();

class local_completion_notification {
    public static function check_completion() {
        global $PAGE, $CFG;
        
        $enabled = get_config('theme_academy_clean', 'completionnotificationsenabled');
        $startdate = get_config('theme_academy_clean', 'completionnotificationsstartdate');

        error_log('headers_sent: ' . print_r($PAGE->state, true));
        if (!$CFG->enablecompletion || !$enabled || !isloggedin() || is_siteadmin() || $PAGE->state != $PAGE::STATE_BEFORE_HEADER) {
            return;
        }

        $startdatets = date_create($startdate)->getTimestamp();

        if (empty($startdatets) || !is_int(intval($startdatets))) {
            return;
        }

        // Skip if displaying the completion notification page or an admin page.
        if ($PAGE->url->out_as_local_url() == '/local/completionnotification/complete.php' ||
            $PAGE->pagelayout == 'admin') {
            return;
        }

        // Regular Completion cron, capturing the output in an output buffer for deletion.
        require_once($CFG->dirroot.'/completion/cron.php');
        ob_start();
        completion_cron_criteria();
        completion_cron_completions();
        ob_end_clean();

        global $DB, $USER;

        $sql = 'SELECT 
                    count(cc.id) as count
                FROM
                    {course_completions} cc
                        LEFT JOIN
                    {local_completionnotification} lcn ON cc.id = lcn.coursecompletionid
                WHERE
                    userid = :userid AND timecompleted > :startdatets AND lcn.coursecompletionid is null;';
        $params = array('userid' => $USER->id, 'startdatets' => $startdatets);

        $newcompletions = $DB->get_record_sql($sql, $params);

        // TODO: remove: error_log('$newcompletions: ' . print_r($newcompletions, true));

        if ($newcompletions->count > 0) {
            $url = new \moodle_url('/local/completionnotification/complete.php',
                    array('wanturl' => $PAGE->url->out_as_local_url()));
            redirect($url);
        }
    }
}
