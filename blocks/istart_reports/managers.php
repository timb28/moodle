<?php

/**
 * Form for users to add and remove their manager
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once("$CFG->dirroot/blocks/istart_reports/lib.php");

$courseid = required_param('courseid', PARAM_INT);

require_login($courseid);

$context = context_user::instance($USER->id);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourse', 'block_istart_reports', '', $courseid);
}

$blockname = get_string('pluginname', 'block_istart_reports');
$header = get_string('headermanagerform', 'block_istart_reports');

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
echo $OUTPUT->heading($header);

echo $OUTPUT->notification(get_string('intromanagerform', 'block_istart_reports'), 'notifymessage');

// Get the user_selectors we will need.
$options = array('multiselect'      => false,
                 'rows'             => 10,
                 'extrafields'      => array('city'),
                 'accesscontext'    => $context);

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
            add_manager($USER, $addmanager);
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