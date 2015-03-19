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

require_once("$CFG->libdir/formslib.php");
require_once("$CFG->dirroot/blocks/istart_reports/lib.php");
require_once("$CFG->dirroot/user/selector/lib.php");

class manageremail_form extends moodleform {

    public function definition() {
        global $CFG, $COURSE, $USER;

        $mform = $this->_form;

        $manager = get_manager_users($USER);
        if (isset($manager)) {
            $managername = $manager[0]->firstname . ' ' . $manager[0]->lastname;
        } else {
            $managername = get_string('nomanager', 'block_istart_reports');
        }

        $mform->addElement('static', 'currentmanager', get_string('labelcurrentmanager', 'block_istart_reports'));
        $mform->setDefault('currentmanager', $managername);

        // Display searchable list of all Moodle users.
        $context = context_course::instance($COURSE->id, MUST_EXIST);
        $options = array('multiselect'      => false,
                         'selected'         => true,
                         'accesscontext'    => $context);
        $managerselector = new manager_selector('manager', $options);
        $managerselectorhtml = $managerselector->display(true);
        $mform->addElement('html', '<p><label for="manager">'.
                get_string('labelselectmanager', 'block_istart_reports') .'</label></p>');
        $mform->addElement('html', $managerselectorhtml);

        $mform->addElement('hidden', 'courseid', $COURSE->id);
        $mform->setType('courseid', PARAM_INT);

        $this->add_action_buttons();
    }


    function validation($data, $files) {
        global $CFG, $COURSE;

        $errors = array();

        // Validate selected manager
        if (confirm_sesskey()) {
            $managerid = optional_param('manager', '', PARAM_ALPHANUM);

            $context = context_course::instance($COURSE->id, MUST_EXIST);
            $options = array('multiselect'      => false,
                             'selected'         => true,
                             'accesscontext'    => $context);
            $managerselector = new manager_selector('manager', $options);
            $selectedmanager = $managerselector->get_selected_users();

            if (!empty($selectedmanager)) {

                foreach ($selectedmanager as $manager) {
                    if ($managerid != $manager->id) {
                        $errors['currentmanager'] = get_string('invalidmanager', 'block_istart_reports');
                    }
                }

                $managerselector->invalidate_selected_users();
            }
        }

        return $errors;
    }
    
}

/**
 * Description of manager_selector
 *
 * @author timbutler
 */
class manager_selector extends user_selector_base {


    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Candidate managers
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                WHERE $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $userscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($userscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $userscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('potmanagersmatching', 'block_istart_reports', $search);
        } else {
            $groupname = get_string('potmanagers', 'block_istart_reports');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'blocks/istart_reports/manageremail_form.php';
        return $options;
    }
}