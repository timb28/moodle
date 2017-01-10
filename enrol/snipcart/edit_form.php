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

        $mform->addElement('header', 'header', get_string('pluginname', 'enrol_snipcart'));

        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'));
        $mform->setType('name', PARAM_TEXT);
        
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'customint1', get_string('socialenrolments', 'enrol_snipcart'), $options);
        
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        $mform->addElement('select', 'status', get_string('status', 'enrol_snipcart'), $options);
        $mform->setDefault('status', $plugin->get_config('status'));

        $mform->addElement('text', 'cost', get_string('cost', 'enrol_snipcart'), array('size'=>6,'maxlength'=>6));
        $mform->setType('cost', PARAM_RAW); // Use unformat_float to get real value.
        $mform->setDefault('cost', format_float($plugin->get_config('cost'), 2, true));

        $snipcartcurrencies = $plugin->get_currencies();
        $mform->addElement('select', 'currency', get_string('currency', 'enrol_snipcart'), $snipcartcurrencies);
        $mform->setDefault('currency', $plugin->get_config('currency'));

        if ($instance->id) {
            $roles = get_default_enrol_roles($context, $instance->roleid);
        } else {
            $roles = get_default_enrol_roles($context, $plugin->get_config('roleid'));
        }
        $mform->addElement('select', 'roleid', get_string('assignrole', 'enrol_snipcart'), $roles);
        $mform->setDefault('roleid', $plugin->get_config('roleid'));


        $mform->addElement('duration', 'enrolperiod', get_string('enrolperiod', 'enrol_snipcart'), array('optional' => true, 'defaultunit' => 86400));
        $mform->setDefault('enrolperiod', $plugin->get_config('enrolperiod'));
        $mform->addHelpButton('enrolperiod', 'enrolperiod', 'enrol_snipcart');

        $mform->addElement('date_time_selector', 'enrolstartdate', get_string('enrolstartdate', 'enrol_snipcart'), array('optional' => true));
        $mform->setDefault('enrolstartdate', 0);
        $mform->addHelpButton('enrolstartdate', 'enrolstartdate', 'enrol_snipcart');

        $mform->addElement('date_time_selector', 'enrolenddate', get_string('enrolenddate', 'enrol_snipcart'), array('optional' => true));
        $mform->setDefault('enrolenddate', 0);
        $mform->addHelpButton('enrolenddate', 'enrolenddate', 'enrol_snipcart');
        
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
        
        list($instance, $plugin, $context) = $this->_customdata;

        if (!empty($data['enrolenddate']) and $data['enrolenddate'] < $data['enrolstartdate']) {
            $errors['enrolenddate'] = get_string('enrolenddaterror', 'enrol_snipcart');
        }

        $cost = str_replace(get_string('decsep', 'langconfig'), '.', $data['cost']);
        if (!is_numeric($cost)) {
            $errors['cost'] = get_string('costerror', 'enrol_snipcart');
        }
        
        // The currency must be unique for this course
        if ($DB->record_exists_select('enrol', 
                'id != ? and courseid = ? and currency = ?',
                array ($instance->id, $context->instanceid, $data['currency']))) {
            $errors['currency'] = get_string('currencyerror', 'enrol_snipcart');
        }
        
        return $errors;
    }
}

