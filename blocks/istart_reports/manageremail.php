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
require_once('manageremail_form.php');

require_login();

$courseid = required_param('courseid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourse', 'block_istart_reports', '', $courseid);
}

$context = context_course::instance($courseid);

$blockname = get_string('pluginname', 'block_istart_reports');
$header = get_string('headermanageremail', 'block_istart_reports');

$returnurl = '/course/view.php?id=' . $courseid;

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->navbar->add($blockname);
$PAGE->navbar->add($header);
$PAGE->set_title($blockname . ': ' . $header);
$PAGE->set_heading($blockname . ': ' . $header);
$PAGE->set_url('/course/view.php', array('id' => $courseid, 'return' => $returnurl));
$PAGE->set_pagetype($blockname);
$PAGE->set_pagelayout('standard');

$mform = new manageremail_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.

  //Set default data (if any)
  $mform->set_data($toform);
  //displays the form
}

echo $OUTPUT->header();
echo $OUTPUT->heading($blockname);

echo html_writer::start_tag('div', array('class' => 'no-overflow'));
$mform->display();
echo html_writer::end_tag('div');
echo $OUTPUT->footer();