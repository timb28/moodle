<?php

/**
 * Adds new instance of enrol_snipcart to specified course
 * or edits current instance.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class enrol_snipcart_edit_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        
        list($instance, $plugin, $context) = $this->_customdata;

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_paypal'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);
        
        // Todo: Add custom form code
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        if (enrol_accessing_via_instance($instance)) {
            $mform->addElement('static', 'selfwarn', get_string('instanceeditselfwarning', 'core_enrol'), get_string('instanceeditselfwarningtext', 'core_enrol'));
        }

        $this->add_action_buttons(true, ($instance->id ? null : get_string('addinstance', 'enrol')));

        $this->set_data($instance);
    }
    
    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        
        // Todo: Add custom validation code
        
        return errors;
    }
}

