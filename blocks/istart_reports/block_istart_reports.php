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

// TODO: Enable completion tracking on this block so we can prevent access to
//       week 1 when no manager email address is present.

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
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->text = '';
        $this->content->footer = '';

        // Get the block's context
        $currentcontext = CONTEXT_BLOCK::instance($this->instance->id);

//        if (! empty($this->config->text)) {
//            $this->content->text = $this->config->text;
//        }

        if (empty($currentcontext)) {
            return $this->content;
        }

        // Manager Report
        // Check if the users role means their progress gets reported to a manager.
        if (has_capability('block/istart_reports:reporttomanager', $currentcontext)) {

            // Have they entered their manager's email address?
            $managers = get_manager_users($USER);

            $this->content->text .= '<div class="studentmanager">';
            if (isset($managers)) {
                $this->content->text .= '<p>'.get_string('studentmanagerreports', 'block_istart_reports').'</p>';
                $this->content->text .= '<ul>';
                foreach ($managers as $manager) {
                    // Display their manager's name with a link to change it
                    $this->content->text .= '<li>' . $manager->firstname . ' ' . $manager->lastname . ' </li>';
                }
                $this->content->text .= '</ul>';
                $this->content->text .= '<p class="text-center"><a href="'.$CFG->wwwroot.'/blocks/istart_reports/managers.php?courseid='.$COURSE->id.'"'
                                                . ' class="btn btn-link">'.get_string('labeleditreportaddress','block_istart_reports').'</a></p></div>';
            } else {
                $this->content->text .= '<p>'.get_string('intromanager', 'block_istart_reports').'</p>';
                $this->content->text .= '<p class="text-center"><a href="'.$CFG->wwwroot.'/blocks/istart_reports/managers.php?courseid='.$COURSE->id.'"'
                                                . ' class="btn btn-primary">'.get_string('labelnewreportaddress','block_istart_reports').'</a></p></div>';
            }

        }

        // Display link to iStart Week Report
        $coursecontext = $this->page->context->get_course_context(false);
                // Check if the users role means their progress gets reported to a manager.
        if (has_capability('report/completion:view', $coursecontext)) {
            $this->content->text .= "<div>TODO: Display link to completion report.</div>";
        }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        // TODO remove when testing complete
        istart_reports_cron();

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => false,
                     'site-index' => false,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => false);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return false;}

    public function cron() {
      mtrace( "Hey, my cron script is running" );
             
      // 
                  
      return true;
    }

}

opcache_reset(); // TODO: Remove this line