<?php

/**
 * Snipcart enrolments plugin settings and presets.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("classes/snipcartaccountsadminsetting.php");


if ($ADMIN->fulltree) {
    
    //require_once($CFG->dirroot . '/enrol/snipcart/tabs.php');

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_snipcart_settings', '', get_string('pluginname_desc', 'enrol_snipcart')));

    $settings->add(new admin_setting_snipcartaccounts());

    // Note: let's reuse the ext sync constants and strings here, internally it is very similar,
    //       it describes what should happen when users are not supposed to be enrolled any more.
    $options = array(
        ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
        ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'),
        ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
    );
    $settings->add(new admin_setting_configselect('enrol_snipcart/expiredaction', get_string('expiredaction', 'enrol_snipcart'), get_string('expiredaction_help', 'enrol_snipcart'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_snipcart_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));
    
    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_snipcart/status',
        get_string('status', 'enrol_snipcart'), get_string('status_desc', 'enrol_snipcart'), ENROL_INSTANCE_DISABLED, $options));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_snipcart/roleid',
            get_string('defaultrole', 'enrol_snipcart'), get_string('defaultrole_desc', 'enrol_snipcart'), $student->id, $options));
    }

    $settings->add(new admin_setting_configduration('enrol_snipcart/enrolperiod',
        get_string('enrolperiod', 'enrol_snipcart'), get_string('enrolperiod_desc', 'enrol_snipcart'), 0));
}
