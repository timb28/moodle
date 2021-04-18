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
 * Library of functions and constants for module courseduration
 *
 * @package mod_courseduration
 * @copyright  2021 AB <virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

define("COURSEDURATION_MAX_NAME_LENGTH", 50);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB, $PAGE, $USER, $CFG;
$manage = new \mod_courseduration\manage();
/**
 * @param object $courseduration
 * @return string
 * @throws coding_exception
 * @uses COURSEDURATION_MAX_NAME_LENGTH
 */
function get_courseduration_name($courseduration): string {
    $name = strip_tags(format_string($courseduration->name , true));
    if (core_text::strlen($name) > COURSEDURATION_MAX_NAME_LENGTH) {
        $name = core_text::substr($name, 0, COURSEDURATION_MAX_NAME_LENGTH)."...";
    }

    if (empty($name)) {
        $name = get_string('modulename' , 'courseduration');
    }

    error_log(" +++ module name:" . print_r($name, true));

    return $name;
}

/**
 * This function returns the start of current active user enrolment.
 * Based on enrollib\enrol_get_enrolment_end($courseid, $userid)
 *
 * It deals correctly with multiple overlapping user enrolments.
 *
 * @param int $courseid
 * @param int $userid
 * @return int|bool timestamp when active enrolment ends, false means no active enrolment now, 0 means never
 * @throws dml_exception
 */
function enrol_get_enrolment_start($courseid, $userid) {
    global $DB;

    $sql = "SELECT ue.*
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.courseid = :courseid)
              JOIN {user} u ON u.id = ue.userid
             WHERE ue.userid = :userid AND ue.status = :active AND e.status = :enabled AND u.deleted = 0
          ORDER BY ue.timestart DESC
             LIMIT 0,1";
    $params = array('enabled'=>ENROL_INSTANCE_ENABLED, 'active'=>ENROL_USER_ACTIVE, 'userid'=>$userid, 'courseid'=>$courseid);
    $userenrolments = $DB->get_records_sql($sql, $params);
    error_log("++++ User enrolments:" . print_r($userenrolments, true));

    if (!$userenrolments) {
        return false;
    } else {
        $earlestenrolment =  reset($userenrolments);
        if ($earlestenrolment->timestart === 0) {
            return $earlestenrolment->timecreated;
        } else {
            return $earlestenrolment->timestart;
        }
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $courseduration
 * @return bool|int
 * @throws dml_exception
 * @global object
 */
function courseduration_add_instance($courseduration) {
    global $DB;

    $courseduration->name = $courseduration->name;
    $courseduration->intro = $courseduration->intro;
    $courseduration->introformat = 1;
    $courseduration->timecreated = time();
    $courseduration->timemodified = time();

    $id = $DB->insert_record('courseduration', $courseduration);

    $completiontimeexpected = !empty($courseduration->completionexpected) ? $courseduration->completionexpected : null;
    \core_completion\api::update_completion_date_event($courseduration->coursemodule, 'courseduration', $id, $completiontimeexpected);

    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $courseduration
 * @return bool
 * @throws dml_exception
 * @global object
 */
function courseduration_update_instance($courseduration):bool {
    global $DB;

    $courseduration->timemodified = time();
    $courseduration->id = $courseduration->instance;

    $completiontimeexpected = !empty($courseduration->completionexpected) ? $courseduration->completionexpected : null;
    \core_completion\api::update_completion_date_event($courseduration->coursemodule, 'courseduration', $courseduration->id, $completiontimeexpected);

    return $DB->update_record("courseduration", $courseduration);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @global object
 */
function courseduration_delete_instance(int $id): bool {
    global $DB;

    if (! $courseduration = $DB->get_record("courseduration", array("id" => $id))) {
        return false;
    }

    $result = true;

    $cm = get_coursemodule_from_instance('courseduration', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'courseduration', $courseduration->id, null);

    if (! $DB->delete_records("courseduration", array("id" => $courseduration->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info|null
 * @throws dml_exception
 * @global object
 */
function courseduration_get_coursemodule_info($coursemodule): cached_cm_info {
    global $DB;

    if ($courseduration = $DB->get_record('courseduration', array('id' => $coursemodule->instance), 'id, name, intro, introformat')) {
//        if (empty($courseduration->name)) {
//            $courseduration->name = "courseduration{$courseduration->id}";
//            $DB->set_field('courseduration', 'name', $courseduration->name, array('id' => $courseduration->id));
//        }
        $info = new cached_cm_info();
        $info->content = format_module_intro('courseduration', $courseduration, $coursemodule->id, false);
        $info->name  = $courseduration->name;
        return $info;
    } else {
        return;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function courseduration_reset_userdata($data):array {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function courseduration_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:
            return true;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_NO_VIEW_LINK:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        default:
            return null;
    }
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param cm_info $cm course module data
 * @param int $from the time to check updates from
 * @param array $filter if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function courseduration_check_updates_since(cm_info $cm, int $from, array $filter = array()): stdClass {
    return course_check_module_updates_since($cm, $from, array(), $filter);
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return \core_calendar\local\event\entities\action_interface|null
 * @throws moodle_exception
 */
function mod_courseduration_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory,
                                                      int $userid = 0) {
    $cm = get_fast_modinfo($event->courseid, $userid)->instances['courseduration'][$event->instance];

    if (!$cm->uservisible) {
        // The module is not visible to the user for any reason.
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new moodle_url('/mod/courseduration/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

/**
 * @throws moodle_exception
 */
function loadscript() {
    global $COURSE, $PAGE, $CFG, $USER;
    $coursetime = $_SESSION['checkcoursetime'];
    $completionduration = $_SESSION['coursetimercompletionduration'];
    $autopausedtime = $_SESSION['forautopaused'];
    $PAGE->requires->js(new moodle_url('https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'), true);
    $PAGE->requires->js(new moodle_url($CFG->wwwroot . '/mod/courseduration/js/courseduration.js') );
    $cssurl = new moodle_url($CFG->wwwroot . '/mod/courseduration/styles.css');
    echo "<link rel='stylesheet' href=".$cssurl.">";
    echo "<input type='hidden' id='coursetime' value='".$coursetime."'>";
    echo "<input type='hidden' id='completionduration' value='".$completionduration."'>";
    echo "<input type='hidden' id='autopaused' value='false'>";
    echo "<input type='hidden' id='currentthemeused' value='".$CFG->theme."'>";
    echo "<input type='hidden' id='autopausedtime' value='".$autopausedtime."'>";
    echo "<input type='hidden' id='moodleversion' value='".$CFG->version."'>";

    $manage = new \mod_courseduration\manage();
    $coursetimer = $manage->getcoursetimer($COURSE->id, $USER->id);
    if ($coursetimer) {
        echo "<input type='hidden' id='coursetimer' value='".$coursetimer->id."'>";
    }
}