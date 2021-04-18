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
 * Custom MOD_COURSEDURATION Runner for mod_courseduration.
 *
 * @package    mod_courseduration
 */
namespace mod_courseduration;
use \stdclass;

define('ENROLMENT_BEFORE_TIMER_ADDED', 1);
define('ENROLMENT_AFTER_TIMER_ADDED',2);

/**
 * Custom MOD_COURSEDURATION\Runner for mod_courseduration.
 *
 * This custom runner just intercepts the init() method, to be able
 * to add all our configuration. The alternative to this is to play
 * with fake $_SERVER['argv' {@see MOD_COURSEDURATION\Config}.
 *
 * @copyright  2021 onwards by AB
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage {

    public $moduleid;

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct() {
        global $DB;
        $module = $DB->get_record('modules', array('name' => get_string('pluginname', 'mod_courseduration')));
        if ($module) {
            $this->moduleid = $module->id;
        }
    }

    /**
     * @param $courseid
     * @param $userid
     * @return false|mixed|stdClass
     * @throws \dml_exception
     */
    public function getcoursetimer($courseid, $userid) {
        GLOBAL $DB;
        return $DB->get_record('courseduration_timers', array('courseid' => $courseid, 'userid' => $userid));
    }

    /**
     * courses function will return site courses list.
     *
     * @return boolean true, false.
     * @throws \dml_exception
     */
    public function createcoursetimer(stdClass $new_coursetimer): bool {
        global $DB;

        $insert = new stdclass();
        
        // Check if user was enrolled in the course before the course timer was added.
        $courseduration = $DB->get_record('courseduration', array('course' =>$new_coursetimer->courseid ));
        $userenrolmentstart = enrol_get_enrolment_start($new_coursetimer->courseid, $new_coursetimer->userid);

        error_log("==== User enrolment check ===");
        if ($userenrolmentstart <= $courseduration->timecreated) {
            error_log(" = Enrolment started before course duration added");
            error_log("   Course duration created: " . print_r($courseduration->timecreated, true));
            error_log("   Enrolment created: " . print_r($userenrolmentstart, true));
            $insert->status = ENROLMENT_BEFORE_TIMER_ADDED;

        } else {
            error_log(" - Enrolment started after course duration added");
            error_log("   Course duration created: " . print_r($courseduration->timecreated, true));
            error_log("   Enrolment created: " . print_r($userenrolmentstart, true));

            $insert->status = ENROLMENT_AFTER_TIMER_ADDED;
        }

        $insert->coursetime = $new_coursetimer->coursetime;
        $insert->coursedurationid = $new_coursetimer->coursedurationid;
        $insert->courseid = $new_coursetimer->courseid;
        $insert->userid = $new_coursetimer->userid;

        $insert->timecreated = time();
        $insert->timemodified = 0; // in milliseconds
        $insert->timecompleted = null;
        return $DB->insert_record('courseduration_timers', $insert);
    }

    /**
     * @param int $coursetimer
     * @param int $coursetimerlength in milliseconds
     * @param int $coursetimerupdated in milliseconds
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function updatecoursetimer(int $coursetimerid, int $coursetimerlength, int $coursetimerupdated): int {
        global $DB, $USER;

        $userid = $USER->id;
        $courseid = $_SESSION['checkcoursemodulecourseid'];

        // Confirm course timer belongs to user
        $coursetimer = $this->getcoursetimer($courseid, $userid);
        if (!$coursetimer) {
            $coursetimer = $this->prepareuser(get_course($courseid), $USER);
            error_log(" +++ coursetimer created by updatecoursetimer:" . print_r($coursetimer, true));
        }

        if ($coursetimer && $coursetimer->status === 0) {
            return false; // timer exists but is not active;
        }

        if ($coursetimerid != $coursetimer->id) {
            throw new \coding_exception('Invalid course timer instance.');
        }

        if ($coursetimer->timemodified < $coursetimerupdated) {
            // Ignore extra time captured
            if ($coursetimer->timemodified > 0 && $coursetimer->timemodified > ($coursetimerupdated - $coursetimerlength)) {
                $coursetimerlength = $coursetimerupdated - $coursetimer->timemodified;
                error_log(" ### Skipping time ###");
                error_log("     ct timemodified:" . print_r($coursetimer->timemodified, true));
                error_log("     ct updated:" . print_r($coursetimerupdated, true));
                error_log("     ct length:" . print_r($coursetimerlength, true));
            }

            $coursetimer->coursetime = $coursetimer->coursetime + $coursetimerlength;
            $coursetimer->timemodified = $coursetimerupdated;
            $DB->update_record('courseduration_timers', $coursetimer);

            if ($coursetimer->timecompleted === null) {
                error_log("=== Checking for timer completion ===");
                error_log(" session coursetimercompletionduration: " . print_r($_SESSION['coursetimercompletionduration'], true));

                // Force module completion for users enrolled before timer was added to course as they
                // have spent uncounted for time in the course
                if ($coursetimer->status == ENROLMENT_BEFORE_TIMER_ADDED) {
                    error_log(" = User enroled BEFORE time added: set complete");
                    $this->setmodulecompleted($coursetimer);
                } else if (round($coursetimer->coursetime / 1000) >= $_SESSION['coursetimercompletionduration']) {
                    error_log(" = Timer is complete!");
                    $this->setmodulecompleted($coursetimer);
                } else {
                    error_log(" x Timer is not complete");
                }
            } else {
                error_log(" >>> Skipping as timer is complete");
            }

        }

        return $coursetimer->coursetime;
    }

    /**
     * @param $courseid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function checkactivityisenable($courseid): bool {
        GLOBAL $DB;

        $courseinstance = $DB->get_record('course_modules', array('course' => $courseid, 'module' => $this->moduleid, 'deletioninprogress' => 0));
        return (bool)$courseinstance;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    function preparepage () {
        global $USER, $COURSE;

        $coursetimer = $this->prepareuser($COURSE, $USER);
        if ($coursetimer) {
            $forautopaused = $this->getautopaused($coursetimer->coursedurationid);
            unset($_SESSION['coursetimercompletionduration']);
            unset($_SESSION['checkcoursemodulecourseid']);
            unset($_SESSION['checkcoursetime']);
            unset($_SESSION['forautopaused']);
            $_SESSION['coursetimercompletionduration'] = $coursetimer->completionduration;
            $_SESSION['checkcoursemodulecourseid'] = $COURSE->id;
            $_SESSION['checkcoursetime'] = $coursetimer->coursetime;
            $_SESSION['forautopaused'] = $forautopaused->autopauseduration;
            loadscript();
            if (!is_siteadmin()) {
                echo "<style>";
                echo "li.activity.courseduration.modtype_courseduration{display:none;}";
                echo "</style>";
            }
        }
    }

    /**
     * @param $courseid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function setmodulecompleted(stdClass $coursetimer): bool {
        global $DB, $USER;

        if (!$coursetimer) { return false; }

        $timercoursemodule = $DB->get_record('course_modules', array('course' => $coursetimer->courseid, 'module' => $this->moduleid, 'deletioninprogress' => 0));

        $completioninfo = new \completion_info(get_course($coursetimer->courseid));

        // Confirm completion is enabled on the course module
        if ($timercoursemodule) {

            $cmc = $completioninfo->get_data($timercoursemodule, false, $coursetimer->userid);
            if ($cmc->completionstate === COMPLETION_COMPLETE) {
                error_log(" == Ignoring existing module completion.");
                $coursetimer->timecompleted = $cmc->timemodified;
                return $DB->update_record('courseduration_timers',$coursetimer);
            }

            error_log(" == Updating module completion.");
            $completioninfo->update_state($timercoursemodule, COMPLETION_COMPLETE, $USER->id);
            $coursetimer->timecompleted = time();
            return $DB->update_record('courseduration_timers',$coursetimer);

        } else {
            return false;
        }
    }

    /**
     * @param $user
     * @param $course
     * @return false|mixed|stdclass
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function prepareuser(stdClass $course, stdClass $user) {
        GLOBAL $DB;

        if (!is_enrolled(\context_course::instance($course->id), $user, false, true)) {
            return false;
        }

        if ($this->checkactivityisenable($course->id)) {
            $sql = "SELECT * FROM {courseduration} WHERE (course = :courseid) AND (status = 1) ORDER BY ID DESC LIMIT 0, 1";
            $params = ['courseid'=>$course->id];
            $courseduration = $DB->get_record_sql($sql, $params);

            if ($courseduration) {
                $coursetimer = $DB->get_record('courseduration_timers', array('coursedurationid' => $courseduration->id, 'userid' => $user->id));
                if ($coursetimer) {
                    $coursetimer->completionduration = $courseduration->completionduration * 60;
                } else {
                    $new_coursetimer = new stdclass();
                    $new_coursetimer->coursetime = 0;

                    $new_coursetimer->courseid = $course->id;
                    $new_coursetimer->coursedurationid = $courseduration->id;
                    $new_coursetimer->userid = $user->id;
                    $newctid = $this->createcoursetimer($new_coursetimer);

                    $coursetimer = $DB->get_record('courseduration_timers', array('id' => $newctid));
                    $coursetimer->completionduration = $courseduration->completionduration * 60;
                }
                return $coursetimer;
            }
        }
        return false;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \dml_exception
     */
    public function getautopaused(int $coursedurationid) {
        GLOBAL $DB;
        return $DB->get_record('courseduration', array('id' => $coursedurationid, 'status' => 1));
    }
}

/**
 * Class observer
 * @package mod_courseduration
 */
class observer {
    /**
     * @throws \coding_exception
     * @throws \moodle_exception
     * @throws \dml_exception
     */
    public static function viewoverride($event) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/courseduration/lib.php');
        $manage = new \mod_courseduration\manage();
        $manage->preparepage();
    }
}