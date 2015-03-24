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

$courseid = required_param('courseid', PARAM_INT);
$context = context_user::instance($USER->id);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourse', 'block_istart_reports', '', $courseid);
}

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

require_login($courseid);

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}


echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('assignto', 'cohort', format_string($cohort->name)));

//echo $OUTPUT->notification(get_string('removeuserwarning', 'core_cohort'));

// Get the user_selectors we will need.
$options = array('multiselect'      => false,
                 'rows'             => 10,
                 'accesscontext'    => $context,
                 'userid'           => $USER->id);

$existingmanagerselector = new manager_existing_selector('removeselect', $options);
$excludeusers = get_excluded_users();
$options['exclude'] = $excludeusers;
$candidatemanagerselector = new manager_candidate_selector('addselect', $options);

// Process incoming user assignments to the cohort

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $candidatemanagerselector->get_selected_users();
    if (!empty($userstoassign)) {

        $newmanagerids = array();

        foreach ($userstoassign as $addmanager) {
            add_manager($USER->id, $addmanager->id);
            $newmanagerids[] = $addmanager->id;
        }

        $candidatemanagerselector->exclude($newmanagerids);

        $candidatemanagerselector->invalidate_selected_users();
        $existingmanagerselector->invalidate_selected_users();
    }
}

// Process removing manager role assignments
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