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
 * Adds new instance of enrol_harcourtsone to specified course
 * or edits current instance.
 *
 * @package    enrol_harcourtsone
 * @copyright  2014 Tim Butler  {@link http://www.harcourtsacademy.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_harcourtsone_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_harcourtsone'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);

        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_harcourtsone'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));
        
        // Registration URL
        $mform->addElement('text', 'customtext1', get_string('customtext1', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customtext1', PARAM_URL);

        // Registration Link Label
        $mform->addElement('text', 'customtext2', get_string('customtext2', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customtext2', PARAM_TEXT);

        // Registration Link Icon
        $mform->addElement('text', 'customtext3', get_string('customtext3', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customtext3', PARAM_TEXT);

        // Information URL
        $mform->addElement('text', 'customtext4', get_string('customtext4', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customtext4', PARAM_URL);
        
        // Information Label
        $mform->addElement('text', 'customchar1', get_string('customchar1', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customchar1', PARAM_TEXT);

        $mform->addElement('header', 'header', get_string('limitinstance', 'enrol_harcourtsone'));

        // Only display to users with this profile field (leave blank to show to all)
        $mform->addElement('text', 'customchar2', get_string('customchar2', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customchar2', PARAM_TEXT);

        // Only display to users with this profile field value (leave blank to show to all)
        $mform->addElement('text', 'customchar3', get_string('customchar3', 'enrol_harcourtsone'), array('size'=>35));
        $mform->setType('customchar3', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }

}
