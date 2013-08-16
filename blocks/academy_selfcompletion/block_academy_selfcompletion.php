<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// NOTICE OF COPYRIGHT                                                   //
//                                                                       //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//          http://moodle.com                                            //
//                                                                       //
// Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com     //
//                                                                       //
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

require_once($CFG->libdir.'/completionlib.php');

/**
 * Self course completion marking
 * Let's a user manually complete a course
 *
 * Will only display if the course has completion enabled,
 * there is a self completion criteria, and the logged in user is yet
 * to complete the course.
 */
class block_academy_selfcompletion extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_academy_selfcompletion');
    }

    function applicable_formats() {
        return array('all' => true, 'mod' => false, 'tag' => false, 'my' => false);
    }

    public function get_content() {
        global $CFG, $USER, $COURSE;
        
        $this->title = get_string('blocktitle', 'block_academy_selfcompletion', $COURSE->shortname);

        // If content is cached
        if ($this->content !== NULL) {
          return $this->content;
        }

        // Create empty content
        $this->content = new stdClass;

        // Can edit settings?
        $can_edit = has_capability('moodle/course:update', context_course::instance($this->page->course->id));

        // Get course completion data
        $info = new completion_info($this->page->course);

        // Don't display if completion isn't enabled!
        if (!completion_info::is_enabled_for_site()) {
            if ($can_edit) {
                $this->content->text = get_string('completionnotenabledforsite', 'completion');
            }
            return $this->content;

        } else if (!$info->is_enabled()) {
            if ($can_edit) {
                $this->content->text = get_string('completionnotenabledforcourse', 'completion');
            }
            return $this->content;
        }

        // Get this user's data
        $completion = $info->get_completion($USER->id, COMPLETION_CRITERIA_TYPE_SELF);

        // Check if self completion is one of this course's criteria
        if (empty($completion)) {
            if ($can_edit) {
                $this->content->text = get_string('selfcompletionnotenabled', 'block_academy_selfcompletion');
            }
            return $this->content;
        }

        // Check this user is enroled
        if (!$info->is_tracked_user($USER->id)) {
            $this->content->text = get_string('nottracked', 'completion');
            return $this->content;
        }

        // Is course complete?
        if ($info->is_course_complete($USER->id)) {
            $this->content->text = get_string('coursealreadycompleted', 'completion');
            return $this->content;

        } else {
            $options = new stdClass();
            $options->newlines = true;    // Convert newlines to <br/> tags
            $this->content->text = format_text($this->config->message, FORMAT_MOODLE, $options);
            
            $completionstate = $completion->is_complete();
            
            $newstate =
                    $completionstate == COMPLETION_COMPLETE
                    ? COMPLETION_INCOMPLETE
                    : COMPLETION_COMPLETE;
            
            $buttonlabel = '';
            if ($completion->is_complete()) {
                $buttonlabel = '&#x27F2; Mark incomplete';
            } else {
                $buttonlabel = '✔ Mark complete';
            }

            $output = '';
            $output .= html_writer::start_tag('form', array('method' => 'post',
                'action' => new moodle_url('/course/togglecompletion.php'),
                'class' => 'togglecompletion singlebutton preventjs'));
            $output .= html_writer::start_tag('div', array(
                'class' => 'text-center'));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'course', 'value' => $COURSE->id));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'hidden', 'name' => 'confirm', 'value' => '1'));
            $output .= html_writer::empty_tag('input', array(
                'type' => 'submit',
                'value' => $buttonlabel,
                'class' => 'btn',
                'aria-live' => 'polite'));
            $output .= '<input type="hidden" value="28" name="course">';
            $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('form');
            
            $this->content->footer = $output;
            
        }

        return $this->content;
    }
}
