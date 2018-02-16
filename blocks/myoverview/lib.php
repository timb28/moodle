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
 * Contains functions called by core.
 *
 * @package    block_myoverview
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* START Academy Patch M#061 My Overview block customisations. */
/**
 * The all-courses view.
 */
define('BLOCK_MYOVERVIEW_ALLCOURSES_VIEW', 'allcourses');
/* END Academy Patch M#061 My Overview block customisations. */

/**
 * The courses view.
 */
define('BLOCK_MYOVERVIEW_COURSES_VIEW', 'courses');

/** START Academy Patch M#061 My Overview block customisations.
 * Sort order.
 */
define('BLOCK_MYOVERVIEW_SORT_DEFAULT', 'default');
define('BLOCK_MYOVERVIEW_SORT_ALPHA', 'alpha');
define('BLOCK_MYOVERVIEW_SORT_ACCESSED', 'accessed');
/* END Academy Patch M#061 */

/**
 * Returns the name of the user preferences as well as the details this plugin uses.
 *
 * @return array
 */
function block_myoverview_user_preferences() {
    $preferences = array();
    $preferences['block_myoverview_last_tab'] = array(
        'type' => PARAM_ALPHA,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEW_ALLCOURSES_VIEW, // Academy Patch M#061
        'choices' => array(BLOCK_MYOVERVIEW_ALLCOURSES_VIEW, BLOCK_MYOVERVIEW_COURSES_VIEW) // Academy Patch M#061
    );

    return $preferences;
}

/* START Academy Patch M#065 Display remote MNet courses in block_myoverview. */
/**
 * Returns a list of user courses including courses on MNet student's local Moodle
 *
 * @return array list of remote courses.
 */
function get_mnet_courses() {
    global $USER;

    // Get other remote courses the user is enrolled in on their local Moodle host.
    $courses = array();
    if (!is_mnet_remote_user($USER)) {
        return;
    }

    $remotecourses = get_my_remotemnetcourses();

    // Other Remote courses will have -ve remoteid as key, so it can be differentiated from normal courses
    foreach ($remotecourses as $id => $remotecourse) {
        $remoteid = $remotecourse->remoteid * -1;
        $remotecourse->id = $remoteid;
        $remotecourse->startdate = null;
        $remotecourse->enddate = null;
        $courses[$remoteid] = $remotecourse;
    }

    return $courses;
}

/**
 * This function classifies a MNet remote course as past or in progress.
 *
 * @param stdClass $course Course record
 * @return string (one of COURSE_TIMELINE_INPROGRESS or COURSE_TIMELINE_PAST)
 */
function remote_course_classify_for_timeline($course) {

    // Course completed.
    if (!empty($course->complete) && $course->complete == 1) {
        return COURSE_TIMELINE_PAST;
    }

    // Everything else is in progress.
    return COURSE_TIMELINE_INPROGRESS;
}
/* END Academy Patch M#065 */
