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
 * Academy Web Services
 *
 * @package    local_academywebservces
 * @copyright  2013 Harcourts International Pty Ltd (http://harcourts.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . "/completionlib.php");
require_once($CFG->dirroot . "/user/lib.php");

class local_academywebservices_external extends external_api {

    const DATEFORMAT = "Y-m-d H:i:s.0000";

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_course_complete_for_user_parameters() {
        return new external_function_parameters(
                array(
                    'courseid' => new external_value(PARAM_INT, 'course id'),
                    'username' => new external_value(PARAM_TEXT, 'username'),
                )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_course_complete_for_user($courseid, $username) {
        global $CFG, $DB, $USER;
        
        // First check global completion
        if (!isset($CFG->enablecompletion) || $CFG->enablecompletion == COMPLETION_DISABLED) {
            throw new moodle_exception('Completion is not enabled sitewide');
        }
        
        $username = strtolower( utf8_decode($username) );

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::get_course_complete_for_user_parameters(), array('courseid'=>$courseid, 'username' => $username));

        //Context validation
        //OPTIONAL but in most web service it should present
        $context = context_user::instance($USER->id);
        self::validate_context($context);

        //Capability checking
        //OPTIONAL but in most web service it should present
        if (!has_capability('moodle/user:viewdetails', $context)) {
            throw new moodle_exception('cannotviewprofile');
        }
        
        // Clean the parameters
        $cleanedcourseid = clean_param($params['courseid'], PARAM_INT);
        if ( $params['courseid'] != $cleanedcourseid) {
                throw new invalid_parameter_exception('The field \'courseid\' value is invalid: ' . $params['courseid'] . '(cleaned value: '.$cleanedcourseid.')');
            }
        
        // Retrieve the course
        try {
            $course = $DB->get_record('course', array('id' => $cleanedcourseid), '*', MUST_EXIST);
        } catch (dml_missing_record_exception $e) {
            throw new invalid_parameter_exception('The field \'courseid\' value is invalid: ' . $params['courseid'] );
        }
        
        // Check that course completion is enabled
        if (!isset($course->enablecompletion)) {
            $course->enablecompletion = $DB->get_field('course', 'enablecompletion', array('id' => $course->id));
        }

        if ($course->enablecompletion == COMPLETION_DISABLED) {
            throw new moodle_exception('Completion is not enabled for this course.');
        }
        
        $cleanedusername = clean_param($params['username'], PARAM_RAW);
        if ( $username != $cleanedusername) {
                throw new invalid_parameter_exception('The field \'username\' value is invalid: ' . $params['username'] . '(cleaned value: '.$cleanedusername.')');
            }
        
        // Retrieve the user ignoring any MNet users that may have identical usernames
        $user = $DB->get_record_select('user', 'username = :username AND auth != "mnet"', array('username' => $cleanedusername), '*', MUST_EXIST);
        $userdetails = user_get_user_details($user, $course);
        $userid = $userdetails['id'];
        
        // Determine whether the user has completed the course
        $iscomplete = 0;
        $completedtime = self::course_complete($courseid, $userid);
        if ($completedtime) {
            $iscomplete = 1;
        }

        date_default_timezone_set('UTC');
        $completeddatetime = date(self::DATEFORMAT, $completedtime);

        return array('complete' => $iscomplete, 'completeddatetime' => $completeddatetime);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_course_complete_for_user_returns() {
        return new external_single_structure(
            array (
                'complete' => new external_value(PARAM_INT, 'complete'),
                'completeddatetime' => new external_value(PARAM_TEXT, 'completeddatetime')
            )
        );
    }
    
    /**
     * Has the supplied user completed this course.
     * Will always return true if the user has completed the course at any
     * time in the past.
     * Returns 0 when course completion is incomplete, in progress or pending
     * Returns 1 when course completion is complete
     *
     * @param int $courseid Course id
     * @param int $userid User id
     * @return datetime
     */
    public static function course_complete($courseid, $userid) {
        $params = array(
            'userid'    => $userid,
            'course'  => $courseid
        );

        $ccompletion = new completion_completion($params);
        return $ccompletion->timecompleted;
    }
    



}
