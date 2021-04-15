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
 * Add courseduration form
 *
 * @package mod_courseduration
 * @copyright  2021 AB <virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Class mod_courseduration_mod_form
 */
class mod_courseduration_mod_form extends moodleform_mod {

    public function definition() {
        global $PAGE;

        $PAGE->force_settings_menu();

        $mform = $this->_form;

        $mform->addElement('header', 'generalhdr', get_string('general'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        $mform->addElement('text', 'completionduration', get_string('courseduration:timerlabel','mod_courseduration'));
        $mform->setDefault('completionduration', 0);
        $mform->setType('completionduration', PARAM_INT);
        $mform->addElement('advcheckbox', 'status', '', get_string('courseduration:timeractivelabel','mod_courseduration'), array('group' => 1), array(0, 1));

        $mform->addElement('text', 'autopauseduration', get_string('courseduration:autopauselabel','mod_courseduration'));
        $mform->setDefault('autopauseduration', 0);
        $mform->setType('autopauseduration', PARAM_INT);

        $mform->addElement('hidden', 'showdescription', 1);
        $mform->setType('showdescription', PARAM_INT);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons(true, false, null);
    }

    /**
     * @return array
     */
    public function add_completion_rules(): array {
        $mform = $this->_form;
        $items = array();

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'completionpass', null, get_string('courseduration:completioncriteria','mod_courseduration'),
                array('group' => 'cpass'));
        $mform->addGroup($group, 'completionpassgroup', 'Complete Time', ' &nbsp; ', false);
        $mform->addHelpButton('completionpassgroup', 'courseduration:completioncriteria', 'mod_courseduration');
        $items[] = 'completionpassgroup';
        return $items;
    }

    /**
     * Called during validation to see whether some module-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionpass']));
    }

    public function data_preprocessing(&$default_values){
        // Set up the completion checkbox which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionpass'] = 1;
    }
}
