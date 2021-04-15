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
        return $DB->get_record('courseduration_timers', array('courseid' => $courseid, 'userid' => $userid, 'status' => 1));
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
        $insert->coursetime = $new_coursetimer->coursetime;
        $insert->coursedurationid = $new_coursetimer->coursedurationid;
        $insert->courseid = $new_coursetimer->courseid;
        $insert->userid = $new_coursetimer->userid;
        $insert->status = 1;
        $insert->createdtime = time();
        $insert->updatedtime = time();
        return $DB->insert_record('courseduration_timers', $insert);
    }

    /**
     * @param int $coursetimer
     * @param int $coursetimerlength
     * @param int $coursetimerupdated
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

        if ($coursetimerid != $coursetimer->id) {
            throw new \coding_exception('Invalid course timer instance.');
        }

        if ($coursetimer->updatedtime < $coursetimerupdated) {
            // Ignore extra time captured
            if ($coursetimer->updatedtime > ($coursetimerupdated - $coursetimerlength)) {
                $coursetimerlengthinseconds = $coursetimerupdated - $coursetimer->updatedtime;
            } else {
                $coursetimerlengthinseconds = $coursetimerlength;
            }

            $coursetimer->coursetime = $coursetimer->coursetime + $coursetimerlengthinseconds;
            $coursetimer->updatedtime = $coursetimerupdated;
            $DB->update_record('courseduration_timers', $coursetimer);

            error_log("=== Checking for timer completion ===");
            error_log(" session coursetimercompletionduration: " . print_r($_SESSION['coursetimercompletionduration'], true));
            error_log(" coursetime: " . print_r($coursetimer->coursetime, true));

            if ($coursetimer->coursetime >= $_SESSION['coursetimercompletionduration']) {
                // TODO: rebuild completion code
                error_log(" = Timer is complete!");
                $this->setmodulecompleted($coursetimer);
            } else {
                error_log(" x Timer is not complete");
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
        global $COURSE, $DB, $USER;

        if (!$coursetimer) { return false; }

        $timercoursemodule = $DB->get_record('course_modules', array('course' => $coursetimer->courseid, 'module' => $this->moduleid, 'deletioninprogress' => 0));

        $completioninfo = new \completion_info(get_course($coursetimer->courseid));

        // Confirm completion is enabled on the course module
        if ($timercoursemodule) {

            $cmc = $completioninfo->get_data($timercoursemodule, false, $coursetimer->userid);
            if ($cmc->completionstate === COMPLETION_COMPLETE) {
                error_log(" == Ignoring existing module completion.");
                return true;
            }

            error_log(" == Updating module completion.");
            $completioninfo->update_state($timercoursemodule, COMPLETION_COMPLETE, $USER->id);
            return true;

//            $prm = array('coursemoduleid' => $timercoursemodule->id, 'userid' => $USER->id);
//            $moduleexisted = $DB->get_record('course_modules_completion', $prm);
//            $mdl = new stdclass();
//
//            if ($moduleexisted && $moduleexisted->completionstate == 0) {
//                $mdl->completionstate = 1;
//                $mdl->viewed = 1;
//                $mdl->timemodified = time();
//                $mdl->id = $moduleexisted->id;
//                $DB->update_record("course_modules_completion", $mdl);
//            } else {
//                $mdl->coursemoduleid = $timercoursemodule->id;
//                $mdl->userid = $USER->id;
//                $mdl->completionstate = 1;
//                $mdl->viewed = 1;
//                $mdl->timemodified = time();
//                $DB->insert_record("course_modules_completion", $mdl);
//            }
        } else {
            return false;
        }


        /*$instanceforthiscourse = '';
        $allcoursemodules = $DB->get_records('course_modules', array('course' => $courseid));
        foreach ($allcoursemodules as $key => $value) {
            $timeridx = $DB->get_record('modules', array('name' => get_string('pluginname', 'mod_courseduration')));
            if ($value->module == $timeridx->id) {
                if ($value->deletioninprogress == 0) {
                    $instanceforthiscourse = $value;
                }
            }
        }

        if ($instanceforthiscourse) {
            $prm = array('coursemoduleid' => $instanceforthiscourse->id, 'userid' => $USER->id);
            $moduleexisted = $DB->get_record('course_modules_completion', $prm);
            $mdl = new stdclass();
            if ($moduleexisted && $moduleexisted->completionstate == 0) {
                $mdl->completionstate = 1;
                $mdl->viewed = 1;
                $mdl->timemodified = time();
                $mdl->id = $moduleexisted->id;
                $DB->update_record("course_modules_completion", $mdl);
            } else {
                $mdl->coursemoduleid = $instanceforthiscourse->id;
                $mdl->userid = $USER->id;
                $mdl->completionstate = 1;
                $mdl->viewed = 1;
                $mdl->overrideby = 0;
                $mdl->timemodified = time();
                $DB->insert_record("course_modules_completion", $mdl);
            }
        }*/
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
                    return $coursetimer;
                } else {
                    $new_coursetimer = new stdclass();
                    $new_coursetimer->coursetime = 0;
                    $new_coursetimer->completionduration = $courseduration->completionduration * 60;
                    $new_coursetimer->courseid = $course->id;
                    $new_coursetimer->coursedurationid = $courseduration->id;
                    $new_coursetimer->userid = $user->id;
                    $newctid = $this->createcoursetimer($new_coursetimer);
                    return $DB->get_record('courseduration_timers', array('coursedurationid' => $newctid));
                }
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
    public static function viewoverride($event) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/courseduration/lib.php');
        $manage = new \mod_courseduration\manage();
        $manage->preparepage();
    }
}