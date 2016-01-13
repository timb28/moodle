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

require('../../config.php');
require_once('edit_form.php');

$courseid   = required_param('courseid', PARAM_INT);
$instanceid = optional_param('id', 0, PARAM_INT); // instanceid

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/snipcart:config', $context);

$PAGE->set_url('/enrol/snipcart/edit.php', array('courseid'=>$course->id, 'id'=>$instanceid));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('snipcart')) {
    redirect($return);
}

$plugin = enrol_get_plugin('snipcart');

if ($instanceid) {
    $instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'snipcart', 'id'=>$instanceid), '*', MUST_EXIST);
    $instance->cost = format_float($instance->cost, 2, true);
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_snipcart_edit_form(NULL, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) {
    if ($instance->id) {
        $reset = ($instance->status != $data->status);

        $instance->status           = $data->status;
        $instance->name             = $data->name;
        $instance->customint1       = $data->customint1;
        $instance->cost             = unformat_float($data->cost);
        $instance->currency         = $data->currency;
        $instance->roleid           = $data->roleid;
        $instance->enrolperiod      = $data->enrolperiod;
        $instance->enrolstartdate   = $data->enrolstartdate;
        $instance->enrolenddate     = $data->enrolenddate;
        $instance->timemodified     = time();
        $DB->update_record('enrol', $instance);

        if ($reset) {
            $context->mark_dirty();
        }

    } else {
        $fields = array(
            'status'=>$data->status, 
            'name'=>$data->name,
            'cost'=>unformat_float($data->cost),
            'currency'=>$data->currency,
            'customint1'=>$data->customint1, 
            'roleid'=>$data->roleid, 
            'enrolperiod'=>$data->enrolperiod, 
            'enrolstartdate'=>$data->enrolstartdate, 
            'enrolenddate'=>$data->enrolenddate);
        $plugin->add_instance($course, $fields);
    }

    redirect($return);
}

$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_snipcart'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_snipcart'));
$mform->display();
echo $OUTPUT->footer();