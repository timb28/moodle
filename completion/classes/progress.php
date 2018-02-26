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
 * Contains class used to return completion progress information.
 *
 * @package    core_completion
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace core_completion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Class used to return completion progress information.
 *
 * @package    core_completion
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress {

    /**
     * Returns the course percentage completed by a certain user, returns null if no completion data is available.
     *
     * @param \stdClass $course Moodle course object
     * @param int $userid The id of the user, 0 for the current user
     * @return null|float The percentage, or null if completion is not supported in the course,
     *         or there are no activities that support completion.
     */
    public static function get_course_progress_percentage($course, $userid = 0) {
        global $CFG, $USER; // Academy Patch M#067
        require_once("{$CFG->libdir}/completionlib.php"); // Academy Patch M#067

        // Make sure we continue with a valid userid.
        if (empty($userid)) {
            $userid = $USER->id;
        }

        $completion = new \completion_info($course);

        // First, let's make sure completion is enabled.
        if (!$completion->is_enabled()) {
            return null;
        }

        // Before we check how many modules have been completed see if the course has.
        if ($completion->is_course_complete($userid)) {
            return 100;
        }

        /* START Academy Patch M#067 Extend core get_course_progress_percentage() to include sub-courses. */
        // Get the number of modules and required courses that have been completed.
        $completed = 0;

        $coursescount = 0;
        foreach ($completion->get_criteria(COMPLETION_CRITERIA_TYPE_COURSE) as $coursecriteria) {
            if (!completion_can_view_data($userid, $coursecriteria->courseinstance)) {
                continue;
            }
            
            // Load course completion
            $params = array(
                'userid'    => $userid,
                'course'    => $coursecriteria->courseinstance
            );
            $ccompletion = new \completion_completion($params);

            if ($ccompletion->is_complete()) {
                $completed++;
            }
            $coursescount++;
        }
        /* END Academy Patch M#067 */

        // Get the number of modules that support completion.
        $modules = $completion->get_activities();
        $count = count($modules) + $coursescount; // Academy Patch M#067
        if (!$count) {
            return null;
        }

        foreach ($modules as $module) {
            $data = $completion->get_data($module, false, $userid);
            $completed += $data->completionstate == COMPLETION_INCOMPLETE ? 0 : 1;
        }

        return ($completed / $count) * 100;
    }
}
