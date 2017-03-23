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
 * Add gradedtask form
 *
 * @package mod_gradedtask
 * @copyright  2006 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_gradedtask_mod_form extends moodleform_mod {

    function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));
        $this->standard_intro_elements(true, get_string('gradedtasktext', 'gradedtask'));

        $mform->addElement('header', 'gradedtasksettings', get_string('gradedtasksettings', 'gradedtask'));
        $mform->setExpanded('gradedtasksettings', true);
        
        $mform->addElement('text', 'maxgrade', get_string('maximumgrade'), array('size' => '10'));
        $mform->setDefault('maxgrade', 100);
        $mform->setType('maxgrade', PARAM_INT);

        $this->standard_coursemodule_elements();
        
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons(true, false, null);

    }
    
}
