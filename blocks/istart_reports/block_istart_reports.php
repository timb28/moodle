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
 * istart_reports block.
 *
 * @package    block_istart_reports
 * @copyright  Harcourts Academy <academy@harcourts.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/istart_reports/lib.php');

class block_istart_reports extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_istart_reports');
    }

    function get_content() {
        global $CFG, $COURSE, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '<p>here</p>';
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content = '';
        if (empty($currentcontext)) {
            return $this->content;
        }
        if ($this->page->course->id == SITEID) {
            $this->context->text .= "site context";
        }

        // Manager Report

        // Is the user a student?
        // Have they entered their manager's email address?
        profile_load_data($USER);
        $manageremailaddress = clean_text($USER->profile_field_manageremailaddress);
        $a = '';
        if ($manageremailaddress == NULL) {
            $a = '<span class="label label-important">' . get_string('noreportaddress', 'block_istart_reports') . '</span>'
                    . ' <a href="'.$CFG->wwwroot.'/blocks/istart_reports/manageremail.php?courseid='.$COURSE->id.'"'
                    . ' class="btn btn-mini">'.get_string('labeleditreportaddress','block_istart_reports').'</a>';
        } else {
            // If yes, display their manager's email address with an edit link
            $a = '<span class="label label-info">' . $manageremailaddress . '</span>'
                    . ' <a href="'.$CFG->wwwroot.'/blocks/istart_reports/manageremail.php?courseid='.$COURSE->id.'"'
                    . ' class="btn btn-mini">'.get_string('labeleditreportaddress','block_istart_reports').'</a>';
        }
        $this->content->text.= get_string('studentmanagerreports', 'block_istart_reports', $a);

        // If no, display a message and text field asking the student to enter their manager's 
           // email address (saved into a new user profile field called 'manageremail')

        // Is the user a trainer?
        // Display link to iStart Week Report (todo: create this as a report plugin)

        clean_reports();
        process_manager_reports();

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return true;
    }

    function has_config() {return true;}

    public function cron() {
      mtrace( "Hey, my cron script is running" );
             
      // 
                  
      return true;
    }
}

opcache_reset(); // TODO: Remove this line