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
        } else {
            throw new \moodle_exception('mod_courseduration not correctly installed.');
        }
    }

    /**
     * courses function will return site courses list.
     *
     * @return array the list of related objects.
     * @throws \dml_exception
     */
    public function courses(): array {
        GLOBAL $DB;
            return $DB->get_records('course', array('visible' => 1, 'category' => 1));
    }

    /**
     * courses function will return site courses list.
     *
     * @param $id
     * @return array the list of related objects.
     * @throws \dml_exception
     */
    public function coursebyid($id):array {
        GLOBAL $DB;
        return $DB->get_record('course', array('visible' => 1, 'id' => $id));
    }

    /**
     * @return bool|StdClass
     * @throws \coding_exception
     */
    public function coursetimerbyid() {
        $id = optional_param('id', 0, PARAM_INT);
        if ($id) {
            return $DB->get_record('courseduration', array('id' => $id));
        } else {
            return false;
        }
    }

    /**
     * course_inform function will return site courses list for the coursetimer form.
     *
     * @return array the list of related objects.
     * @throws \coding_exception
     */
    public function course_inform(): array {
        $id = optional_param('id', 0, PARAM_INT);
        $course = $this->courses();
        $coursetimer = $this->coursetimer();
        $courseoptions = array();
        $courseoptions[''] = '';
        foreach ($course as $ct) {
            $courseoptions[$ct->id] = $ct->fullname;
        }
        if (count($coursetimer) > 0) {
            foreach ($coursetimer as $ct) {
                if ($id != $ct->id) {
                    unset($courseoptions[$ct->id]);
                }
            }
        }
        return $courseoptions;
    }

    /**
     * @return array course options
     * @throws \dml_exception
     */
    public function updatecourse_inform(): array {
        $course = $this->courses();
        $coursetimer = $this->coursetimer();
        $courseoptions = array();
        $courseoptions[''] = '';
        foreach ($course as $ct) {
            $courseoptions[$ct->id] = $ct->fullname;
        }
        if (count($coursetimer) > 0) {
            foreach ($coursetimer as $ct) {
                unset($courseoptions[$ct->id]);
            }
        }
        return $courseoptions;
    }

    /**
     * courses function will return site courses list.
     *
     * @return array the list of related objects.
     * @throws \dml_exception
     */
    public function coursetimer(): array {
        GLOBAL $DB;
        return $DB->get_records('courseduration', array());
    }

    /**
     * @param $courseid
     * @param $userid
     * @return false|mixed|stdClass
     * @throws \dml_exception
     */
    public function getcoursetimerinstance($courseid, $userid) {
        GLOBAL $DB;
        return $DB->get_record('coursetimer_instance', array('courseid' => $courseid, 'userid' => $userid, 'status' => 1));
    }

    /**
     * @param $id
     * @return false|mixed|stdClass
     * @throws \dml_exception
     */
    public function getcoursetimerbyid($id) {
        GLOBAL $DB;
        return $DB->get_record('courseduration', array('id' => $id));
    }

    /**
     * courses function will return site courses list.
     *
     * @return boolean true, false.
     * @throws \dml_exception
     */
    public function createcoursetimerinstance(stdClass $new_coursetimerinstance): bool {
        global $DB;
        $insert = new stdclass();
        $insert->availabletime = $new_coursetimerinstance->availabletime;
        $insert->ctimerinstanceid = $new_coursetimerinstance->ctimerinstanceid;
        $insert->courseid = $new_coursetimerinstance->courseid;
        $insert->userid = $new_coursetimerinstance->userid;
        $insert->status = 1;
        $insert->createdtime = time();
        $insert->updatedtime = time();
        return $DB->insert_record('coursetimer_instance', $insert);
    }

    /**
     * courses function will return site courses list.
     *
     * @param $data
     * @return boolean true, false.
     * @throws \dml_exception
     */
    public function createcoursetimer($data): bool {
        global $DB;
        foreach ($data->course as $val) {
            $insert = new stdclass();
            $insert->completiontimer = $data->completiontimer;
            $insert->courseid = $val;
            $insert->coursename = $this->coursebyid($val)->fullname;
            $insert->autopaused = $data->autopaused;
            $insert->status = $data->status;
            $insert->createdtime = time();
            $insert->updatedtime = time();
            $DB->insert_record('courseduration', $insert);
        }
        return true;
    }

    /**
     * courses function will return site courses list.
     * This method is used logic to manipulate timer according to the update of time
     * if old time is greater or lower than new time
     * then it will update the time in the current active course view.
     * @return boolean true, false.
     * @throws \dml_exception
     */
    public function Replaced_updatecoursetimer($data): bool {
        global $DB;

        $update = new stdclass();
        $update->id = $data->id;
        $update->completiontimer = $data->completiontimer;
        $update->courseid = $data->course;
        $update->coursename = $this->coursebyid($data->course)->fullname;
        $update->status = $data->status;
        $update->autopaused = $data->autopaused;
        $update->updatedtime = time();
        if ($data->oldtimer != $data->completiontimer) {
            $prm = array('ctimerinstanceid' => $data->id, 'status' => 1 );
            $existing = $DB->get_records('coursetimer_instance', $prm);
            if (count($existing) > 0) {
                foreach ($existing as $vl) {
                    if ($data->completiontimer == 0) {
                        $newtimer = 0;
                    } else if ($data->oldtimer > $data->completiontimer) {
                        $newtimer = $vl->availabletime - ( $data->completiontimer * 60 );
                    } else if ($data->oldtimer < $data->completiontimer) {
                        $newtimer = $vl->availabletime + ( $data->completiontimer * 60 );
                    } else {
                        $newtimer = $vl->availabletime;
                    }
                    $change = new stdclass();
                    $change->id = $vl->id;
                    $change->availabletime = $newtimer;
                    if ($data->completiontimer == 0) {
                        $change->status = 0;
                    }
                    $DB->update_record('coursetimer_instance', $change);
                }
            }
        }
        return $DB->update_record('courseduration', $update);
    }

    /**
     * courses function will return site courses list.
     *
     * @param $id
     * @return boolean true, false.
     * @throws \dml_exception
     */
    public function deletecoursetimer($id): bool {
        global $DB;
        return $DB->delete_records('courseduration',array('id'=>$id));
    }

    /**
     * @param int $coursetimerinstance
     * @param int $coursetimerlength
     * @param int $coursetimerupdated
     * @return int
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function updatecoursetimer(int $coursetimerinstanceid, int $coursetimerlength, int $coursetimerupdated): int {
        global $COURSE, $DB, $USER;

        $userid = $USER->id;
        $courseid = $_SESSION['checkcoursemodulecourseid'];

        // Confirm course timer belongs to user
        $coursetimerinstance = $this->getcoursetimerinstance($courseid, $userid);

        if ($coursetimerinstanceid != $coursetimerinstance->id) {
            throw new \coding_exception('Invalid course timer instance.');
        }

        if ($coursetimerinstance->updatedtime < $coursetimerupdated) {
            // Ignore extra time captured
            if ($coursetimerinstance->updatedtime > ($coursetimerupdated - $coursetimerlength)) {
                $coursetimerlengthinseconds = $coursetimerupdated - $coursetimerinstance->updatedtime;
            } else {
                $coursetimerlengthinseconds = $coursetimerlength;
            }

            $coursetimerinstance->availabletime = $coursetimerinstance->availabletime + $coursetimerlengthinseconds;
            $coursetimerinstance->updatedtime = $coursetimerupdated;
            $DB->update_record('coursetimer_instance', $coursetimerinstance);

            if ($coursetimerinstance->availabletime <= 0) {
                // TODO: rebuild completion code
                //$this->setcoursecompletedbyuser($courseid);
            }
        }

        return $coursetimerinstance->availabletime;
    }

    /**
     * @return bool|stdclass
     * @throws \dml_exception
     * @throws \coding_exception
     */
    /*public function coursetimercountdown() {
        global $DB, $USER;
        if (isset($_SESSION['checkcoursemodulecourseid'])) {
            $userid = $USER->id;
            $courseid = $_SESSION['checkcoursemodulecourseid'];
            if ($this->checkactivityisenable($courseid)) {
                $sqlt1 = "SELECT * FROM {courseduration} WHERE (course = :courseid) AND (status = 1) ORDER BY ID DESC LIMIT 0, 1";
                $params1 = ['courseid' => $courseid];
                $courseduration = $DB->get_record_sql($sqlt1, $params1);
                $arrprm = array('courseid' => $courseid, 'userid' => $userid, 'ctimerinstanceid' => $courseduration->id);
                $coursedurationinstance = $DB->get_record('coursetimer_instance', $arrprm);
                if ($coursedurationinstance->availabletime > 0) {
                    $coursedurationinstance->availabletime = $coursedurationinstance->availabletime - 2;
                    $DB->update_record('coursetimer_instance', $coursedurationinstance);
                    return $coursedurationinstance;
                } else {
                    $this->setcoursecompletedbyuser($courseid);
                    return false;
                }
            }*/
//            } else if ($this->checkactivityisinmodule($courseid)) {
//                $courseid = $this->checkactivityisinmodule($courseid)->course;
//                $sqlt1 = "SELECT * FROM {courseduration} WHERE (course = :courseid) AND (status = 1) ORDER BY ID DESC LIMIT 0, 1";
//                $params1 = ['courseid'=>$courseid];
//                $coursetim = $DB->get_record_sql($sqlt1, $params1);
//                $arrprm = array('courseid' => $courseid, 'userid' => $userid, 'ctimerinstanceid' => $coursetim->id);
//                $currenttimer = $DB->get_record('coursetimer_instance', $arrprm);
//                if ($currenttimer->availabletime > 0) {
//                    $currenttimer->availabletime = $currenttimer->availabletime - 2;
//                    $currenttimer->availabletime = $currenttimer->availabletime - 2;
//                    $DB->update_record('coursetimer_instance',$currenttimer);
//                    return $currenttimer;
//                } else {
//                    $this->setcoursecompletedbyuser($courseid);
//                    return false;
//                }
//            }
/*        }
    }*/

    /**
     * @param $courseid
     * @return bool
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function checkactivityisenable($courseid): bool {
        GLOBAL $DB;

        $courseinstance = $DB->get_record('course_modules', array('course' => $courseid, 'module' => $this->moduleid, 'deletioninprogress' => 0));
        return $courseinstance ? true : false;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    function preparepage () {
        global $USER, $COURSE;

        $coursetimerinstance = $this->prepareuser($COURSE, $USER);
        if ($coursetimerinstance) {
            $forautopaused = $this->getautopaused($coursetimerinstance->ctimerinstanceid);
            unset($_SESSION['coursetimercompletiontime']);
            unset($_SESSION['checkcoursemodulecourseid']);
            unset($_SESSION['checkCourseTimerAvailabletime']);
            unset($_SESSION['forautopaused']);
            $_SESSION['coursetimercompletiontime'] = $coursetimerinstance->completiontime;
            $_SESSION['checkcoursemodulecourseid'] = $COURSE->id;
            $_SESSION['checkCourseTimerAvailabletime'] = $coursetimerinstance->availabletime;
            $_SESSION['forautopaused'] = $forautopaused->autopaused;
            loadscript();
            if (!is_siteadmin()) {
                echo "<style>";
                echo "li.activity.courseduration.modtype_courseduration{display:none;}";
                echo "</style>";
            }
        }
    }

    /**
     * @param $cmid
     * @return mixed|string
     * @throws \coding_exception
     * @throws \dml_exception
     */
//    public function checkactivityisinmodule($cmid):string {
//        GLOBAL $DB;
//
//        $instanceforthiscourse = '';
//        $modulecourse = $DB->get_record('course_modules', array('id' => $cmid));
//        $allcoursemodules = $DB->get_records('course_modules', array('course' => $modulecourse->course, 'deletioninprogress' => 0));
//        foreach ($allcoursemodules as $key => $value) {
//            $timeridx = $DB->get_record('modules', array('name' => get_string('pluginname', 'mod_courseduration')));
//            if ($value->module == $timeridx->id) {
//                $instanceforthiscourse = $value;
//            }
//        }
//        return $instanceforthiscourse;
//    }

    /**
     * @param $courseid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function setcoursecompletedbyuser($courseid) {
        global $DB, $USER;

        $timercoursemodule = $DB->get_record('course_modules', array('course' => $courseid, 'module' => $this->moduleid, 'deletioninprogress' => 0));

        if ($timercoursemodule) {
            $prm = array('coursemoduleid' => $timercoursemodule->id, 'userid' => $USER->id);
            $moduleexisted = $DB->get_record('course_modules_completion', $prm);
            $mdl = new stdclass();

            if ($moduleexisted && $moduleexisted->completionstate == 0) {
                $mdl->completionstate = 1;
                $mdl->viewed = 1;
                $mdl->timemodified = time();
                $mdl->id = $moduleexisted->id;
                $DB->update_record("course_modules_completion", $mdl);
            } else {
                $mdl->coursemoduleid = $timercoursemodule->id;
                $mdl->userid = $USER->id;
                $mdl->completionstate = 1;
                $mdl->viewed = 1;
                $mdl->timemodified = time();
                $DB->insert_record("course_modules_completion", $mdl);
            }
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
                $coursetimerinstance = $DB->get_record('coursetimer_instance', array('ctimerinstanceid' => $courseduration->id, 'userid' => $user->id));
                if ($coursetimerinstance) {
                    $coursetimerinstance->completiontime = $courseduration->completiontimer * 60;
                    return $coursetimerinstance;
                } else {
                    $new_coursetimerinstance = new stdclass();
                    $new_coursetimerinstance->availabletime = 0;
                    $new_coursetimerinstance->completiontime = $courseduration->completiontimer * 60;
                    $new_coursetimerinstance->courseid = $course->id;
                    $new_coursetimerinstance->ctimerinstanceid = $courseduration->id;
                    $new_coursetimerinstance->userid = $user->id;
                    $newctid = $this->createcoursetimerinstance($new_coursetimerinstance);
                    return $DB->get_record('coursetimer_instance', array('ctimerinstanceid' => $newctid));
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
    public function getautopaused($id) {
        GLOBAL $DB;
        return $DB->get_record('courseduration', array('id' => $id, 'status' => 1));
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