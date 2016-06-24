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
 * @package    theme_academy_clean
 * @copyright  2016 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_academy_clean\completion_notification;

require_once($CFG->libdir.'/completionlib.php');

defined('MOODLE_INTERNAL') || die();

class local_completion_notification {
    public static function check_completion() {
        global $PAGE, $CFG;
        
//        $course = $DB->get_record('course', array('id' => '29'));
//        $completion = new \completion_info($course);
//        error_log('get_completions(): ' . print_r($completion->get_completions('3753'), true));
        

        
        $enabled = get_config('theme_academy_clean', 'completionnotificationsenabled');
        $startdate = get_config('theme_academy_clean', 'completionnotificationsstartdate');

        if (!$CFG->enablecompletion || !$enabled || !isloggedin() || is_siteadmin() || $PAGE->state != $PAGE::STATE_BEFORE_HEADER) {
            return;
        }

        $startdatets = date_create($startdate)->getTimestamp();

        if (empty($startdatets) || !is_int(intval($startdatets))) {
            return;
        }

        // Skip if displaying the completion notification page or an admin page.
        if ($PAGE->url->out_as_local_url() == '/theme/academy_clean/complete.php' ||
            $PAGE->pagelayout == 'admin') {
            return;
        }

        // Regular Completion cron, capturing the output in an output buffer for deletion.
//        require_once($CFG->dirroot.'/completion/cron.php');
//        ob_start();
// TODO        completion_cron_criteria();
// TODO       completion_cron_completions();
//        ob_end_clean();

        global $DB, $USER;
        require_once($CFG->dirroot.'/completion/completion_criteria_completion.php');
        
        $completionstoupdate = local_completion_notification::get_incomplete_completions($USER->id);
        foreach ($completionstoupdate as $record) {
            error_log('$record: ' . print_r($record, true));
            $completion = new \completion_criteria_completion((array) $record, DATA_OBJECT_FETCH_BY_KEY);
            $completion->mark_complete($record->timecompleted);
        }
        $completionstoupdate->close();
        
        

        $sql = 'SELECT 
                    count(cc.id) as count
                FROM
                    {course_completions} cc
                        LEFT JOIN
                    {course_completion_notifs} ccn ON cc.course = ccn.courseid
                WHERE
                    cc.userid = :userid AND cc.timecompleted > :startdatets AND ccn.courseid is null;';
        $params = array('userid' => $USER->id, 'startdatets' => $startdatets);

        $newcompletions = $DB->get_record_sql($sql, $params);

        error_log('$newcompletions: ' . print_r($newcompletions, true));

        if ($newcompletions->count > 0) {
            $url = new \moodle_url('/theme/academy_clean/complete.php',
                    array('wanturl' => $PAGE->url->out_as_local_url()));
// TODO: uncomment:            redirect($url);
        }
    }
    
    public static function get_incomplete_completions($userid, $courseid = null) {
        global $DB;

        // Get all users who meet this criteria
        $sql = '
            SELECT DISTINCT
                c.id AS course,
                cr.id AS criteriaid,
                mc.userid AS userid,
                mc.timemodified AS timecompleted
            FROM
                {course_completion_criteria} cr
            INNER JOIN
                {course} c
             ON cr.course = c.id
            INNER JOIN
                {context} con
             ON con.instanceid = c.id
            INNER JOIN
                {course_modules_completion} mc
             ON mc.coursemoduleid = cr.moduleinstance
            LEFT JOIN
                {course_completion_crit_compl} cc
             ON cc.criteriaid = cr.id
            AND cc.userid = mc.userid
            WHERE
                cr.criteriatype = '.COMPLETION_CRITERIA_TYPE_ACTIVITY.'
            AND con.contextlevel = '.CONTEXT_COURSE.'
            AND mc.userid = :userid
            AND c.enablecompletion = 1
            AND cc.id IS NULL
            AND (
                mc.completionstate = '.COMPLETION_COMPLETE.'
             OR mc.completionstate = '.COMPLETION_COMPLETE_PASS.'
             OR mc.completionstate = '.COMPLETION_COMPLETE_FAIL.'
                )
        ';

        // Get records for updating
        $params = array('userid' => $userid);
        return $DB->get_recordset_sql($sql, $params);
    }
}
