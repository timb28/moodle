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
 * Academy Score Block.
 *
 * @package   block_academy_score
 * @author    Tim Butler <tim.butler@harcourts.net>, Karen Holland <kholland.dev@gmail.com>, Mei Jin, Jiajia Chen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->libdir}/gradelib.php");
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once $CFG->dirroot.'/grade/report/overview/lib.php';
require_once $CFG->dirroot.'/grade/lib.php';

class block_academy_score extends block_base {
	public function init() {
		$this->title = get_string('blocktitle', 'block_academy_score');
	}
	
	public function get_content() {
		global $DB, $USER, $COURSE;
	
		if ($this->content !== null) {
			return $this->content;
		}
 
		$this->content         =  new stdClass;
        $total_score = 0;

		/// return tracking object
		$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'overview', 'userid'=>$USER->id));
 
		// Create a report instance
		$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
		$report = new grade_report_overview($USER->id, $gpr, $context);

		$newdata=$this->grade_data($report);
		if ($newdata != null)
		{
            $total_score = ceil($newdata);
            $this->content->text.= "<div class=\"score-total\">{$total_score}</div>";
		}
		else
		{
			$this->content->text.=$newdata;
		}
        
		return $this->content;
	}

	public function instance_allow_multiple() {
		return false;
	}
	
	public function grade_data($report) {
		global $CFG, $DB, $OUTPUT;
		$gradedata = null;
		
		if ($courses = enrol_get_users_courses($report->user->id, false, 'id, shortname, showgrades')) {
			$numusers = $report->get_numusers(false);
            
            // Only count online certificate courses
            $valid_course_categories = array();

            if (!empty($this->config->coursecategories)) {
                $valid_course_categories = $this->config->coursecategories;
            }

            $gradedata = 0;
            
			foreach ($courses as $course) {
				if (!$course->showgrades) {
					continue;
				}
                
                if (!in_array($course->category, $valid_course_categories)) {
                    continue;
                }

				$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

				if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
					// The course is hidden and the user isn't allowed to see it
					continue;
				}

				// Get course grade_item
				$course_item = grade_item::fetch_course_item($course->id);

				// Get the stored grade
				$course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$report->user->id));
				$course_grade->grade_item =& $course_item;
				$finalgrade = $course_grade->finalgrade;

				$gradedata+= $finalgrade;
			}
			
			if ($gradedata == null) {
				return $OUTPUT->notification(get_string('nocourses', 'grades'));
			} else {
				return $gradedata;
			}
		} else {
			return $OUTPUT->notification(get_string('nocourses', 'grades'));
		}
	}
}

?>
