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
 * training_pathways block caps.
 *
 * @package     block_training_pathways
 * @author      Tim Butler
 * @copyright   2017 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use block_training_pathways\output\recommended_paths;

/**
 * TODO: Don't display empty block.
 */

class block_training_pathways extends block_base {

    function init() {
        $this->title = get_string('blocktitle', 'block_training_pathways');
    }

    function get_content() {
        global $CFG, $PAGE;
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content         = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';
        
        $recommended_paths = new \block_training_pathways\output\recommended_paths();
        //error_log('recommended paths: ' . print_r($recommended_paths, true));
        //$this->content->text = $recommended_paths->output_paths();
        //$output = $PAGE->get_renderer('block_training_pathways\output\block_training_pathways_renderer');
        $output = $PAGE->get_renderer('block_training_pathways');
        $this->content->text = $output->render($recommended_paths);

        
        return $this->content;
    }

    public function applicable_formats() {
        return array('my' => true);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return false;}

}
