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
 * Harcourts enrolment plugin.
 *
 * @package    enrol_harcourtsone
 * @copyright  2014 Tim Butler {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Harcourts enrolment plugin implementation.
 * @author Tim Butler
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_harcourtsone_plugin extends enrol_plugin {

    public function enrol_user(stdClass $instance, $userid, $roleid = null, $timestart = 0, $timeend = 0, $status = null, $recovergrades = null) {
        // no real enrolments here!
        return;
    }

    public function unenrol_user(stdClass $instance, $userid) {
        // nothing to do, we never enrol here!
        return;
    }

    public function roles_protected() {
        // Users can't tweak the roles later.
        return true;
    }

    public function allow_enrol(stdClass $instance) {
        // Users with enrol cap can't enrol other users manually manually.
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // Users with unenrol cap can't unenrol other users manually manually.
        return false;
    }

    public function allow_manage(stdClass $instance) {
        // Users with manage cap can't tweak period and status.
        return false;
    }

    public function show_enrolme_link(stdClass $instance) {
         return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
        global $DB;

        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/harcourtsone:config', $context)) {
            return NULL;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'harcourtsone'))) {
            return NULL;
        }

        return new moodle_url('/enrol/harcourtsone/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Add new instance of enrol plugin.
     * @param stdClass $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;

        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'harcourtsone'))) {
            // only one instance allowed, sorry
            return NULL;
        }

        return parent::add_instance($course, $fields);
    }
    
    /**
     * It isn't possible to hide/show enrol instance via standard UI.
     *
     * @param stdClass $instance
     * @return bool
     */
    public function can_hide_show_instance($instance) {
        return false;
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    function enrol_page_hook(stdClass $instance) {
        global $COURSE, $OUTPUT, $CFG, $PAGE;

        ob_start();
        
        if (isguestuser()) { // force login only for guest user, not real users with guest role
            if ($CFG->loginhttps) {
                // This actually is not so secure ;-), 'cause we're
                // in unencrypted connection...
                $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
            } else {
                $wwwroot = $CFG->wwwroot;
            }
            redirect($wwwroot.'/login/');
        }

        if ($instance->customtext1 == NULL) { // no cost, other enrolment methods (instances) should be used
            if (empty($COURSE->summary)) {
                echo html_writer::tag('p', get_string('nourl', 'enrol_harcourtsone'));
            } else {
                $coursecontext = \context_course::instance($COURSE->id);
                $options = array('filter' => false, 'overflowdiv' => true, 'noclean' => true, 'para' => false);
                $summary = file_rewrite_pluginfile_urls($COURSE->summary, 'pluginfile.php', $coursecontext->id, 'course', 'summary', null);
                $summary = format_text($summary, $COURSE->summaryformat, $options, $COURSE->id);
                
                echo html_writer::start_div('alert alert-info');
                echo html_writer::tag('p', get_string("enrolinstructions", "enrol_harcourtsone"));
                echo html_writer::div($summary);
                echo html_writer::end_div();
            }
        } else {
            echo html_writer::start_div('mdl-align alert alert-info');
            echo html_writer::tag('p', get_string("enrolinstructions", "enrol_harcourtsone"));
            echo html_writer::start_tag('p');
            echo html_writer::link($instance->customtext1, get_string("enrolbutton", "enrol_harcourtsone"), array('class'=>'btn'));
            echo html_writer::end_tag('p');
            echo html_writer::end_div();
        }
        
        /* Match the padding of 'alert' to  vertically align the elements. */
        echo html_writer::start_div('mdl-align alert-block', array('style'=>'padding: 8px 35px 8px 14px;'));
        echo html_writer::tag('p', get_string("reloadinstructions", "enrol_harcourtsone"));
        echo html_writer::start_tag('p');
        echo html_writer::link($PAGE->url, get_string("reloadbutton", "enrol_harcourtsone"), array('class'=>'btn'));
        echo html_writer::end_tag('p');
        echo html_writer::end_div();


        return $OUTPUT->box(ob_get_clean());
    }

    /**
     * Returns edit icons for the page with list of instances.
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'harcourtsone') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/harcourtsone:config', $context)) {
            $editlink = new moodle_url("/enrol/harcourtsone/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                    array('class' => 'iconsmall')));
        }

        return $icons;
    }
}
