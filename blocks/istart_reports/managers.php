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
 * Add and edit manager email addresses iStart reports are sent to
 *
 * @package   block_istart_reports
 * @copyright Harcourts Academy <academy@harcourts.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->dirroot/blocks/istart_reports/lib.php");

//require_login();
//
//$courseid = required_param('courseid', PARAM_INT);
//
//if (!$course = $DB->get_record('course', array('id' => $courseid))) {
//    print_error('nocourse', 'block_istart_reports', '', $courseid);
//}
//
//$context = context_course::instance($courseid);
//
//$blockname = get_string('pluginname', 'block_istart_reports');
//$header = get_string('headermanageremail', 'block_istart_reports');
//
//$returnurl = $CFG->wwwroot.'/course/view.php?id=' . $courseid;
//
//$PAGE->set_context($context);
//$PAGE->set_course($course);
//$PAGE->navbar->add($blockname);
//$PAGE->navbar->add($header);
//$PAGE->set_title($blockname . ': ' . $header);
//$PAGE->set_heading($blockname . ': ' . $header);
//$PAGE->set_url('/course/view.php', array('id' => $courseid, 'return' => $returnurl));
//$PAGE->set_pagetype('istart-reports');
//$PAGE->set_pagelayout('standard');
//
//$mform = new managers_form();
//
////Form processing and displaying is done here
//if ($mform->is_cancelled()) {
//    // Return to the course, if cancel button is pressed
//    redirect($returnurl);
//} else if ( ($fromform = $mform->get_data()) && confirm_sesskey() ) {
//    // Process validated form data.
//    $success = set_manager($USER, optional_param('manager', '', PARAM_ALPHANUM));
//    redirect($returnurl);
//}
//
//echo $OUTPUT->header();
//echo $OUTPUT->heading($header);
//
//echo html_writer::start_tag('div', array('class' => 'no-overflow'));
//echo html_writer::tag('p', get_string('intromanager','block_istart_reports'), array('class'=>'intromanager'));
//$mform->display();
//echo html_writer::end_tag('div');
//
//echo $OUTPUT->footer();

$courseid = required_param('courseid', PARAM_INT);

require_login();

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourse', 'block_istart_reports', '', $courseid);
}

$context = context_course::instance($courseid);

$blockname = get_string('pluginname', 'block_istart_reports');
$header = get_string('headermanageremail', 'block_istart_reports');

$returnurl = $CFG->wwwroot.'/course/view.php?id=' . $courseid;

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/blocks/istart_reports/managers.php', array('courseid'=>$courseid));
$PAGE->set_pagetype('istart-reports');
$PAGE->set_pagelayout('standard');

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}


echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('assignto', 'cohort', format_string($cohort->name)));

//echo $OUTPUT->notification(get_string('removeuserwarning', 'core_cohort'));

// Get the user_selector we will need.
$selectusers = array();
$existingmanagers = get_manager_users($USER);
if (isset($existingmanagers)) {
    foreach ($existingmanagers as $manager) {
        $selectusers[$manager->id] = $manager->id;
    }
}
$excludeusers = get_excluded_users();
$options = array('multiselect'      => false,
                 'preserveselected' => true,
                 'rows'             => 10,
                 'selected'         => $selectusers,
                 'accesscontext'    => $context,
                 'userid'           => $USER->id);

$existingmanagerselector = new manager_existing_selector('removeselect', $options);
$options['exclude'] = $excludeusers;
$candidatemanagerselector = new manager_candidate_selector('addselect', $options);

// Process incoming user assignments to the cohort

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $candidatemanagerselector->get_selected_users();
    if (!empty($userstoassign)) {

        foreach ($userstoassign as $addmanager) {
            add_manager($USER->id, $addmanager->id);
        }

        $candidatemanagerselector->invalidate_selected_users();
        $existingmanagerselector->invalidate_selected_users();
    }
}

// Process removing user assignments to the cohort
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existingmanagerselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removemanager) {
            remove_manager($USER->id, $removemanager->id);
        }
        $candidatemanagerselector->invalidate_selected_users();
        $existingmanagerselector->invalidate_selected_users();
    }
}

// Print the form.
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
  <input type="hidden" name="courseid" value="<?php echo $courseid ?>" />

  <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php print_string('labelcurrentmanager', 'block_istart_reports'); ?></label></p>
          <?php $existingmanagerselector->display() ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')).'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect"><?php print_string('labelselectmanager', 'block_istart_reports'); ?></label></p>
          <?php $candidatemanagerselector->display() ?>
      </td>
    </tr>
    <tr><td colspan="3" id='backcell'>
      <input type="submit" name="cancel" value="<?php p(get_string('backtocourse', 'block_istart_reports')); ?>" />
    </td></tr>
  </table>
</div></form>

<?php

echo $OUTPUT->footer();




opcache_reset(); // TODO: Remove this line