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

class managers_form extends moodleform {

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

        $selectusers = array();
        $existingmanagers = get_manager_users($USER);
        if (isset($existingmanagers)) {
            foreach ($existingmanagers as $manager) {
                $selectusers[$manager->id] = $manager->id;
            }
        }
        $excludeusers = $this->get_excluded_users();
        $options = array('multiselect'      => false,
                         'preserveselected' => true,
                         'rows'             => 10,
                         'selected'         => $selectusers,
                         'exclude'          => $excludeusers,
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

    private function get_excluded_users() {
        global $DB, $CFG;

        // Exclude site administrators
        $siteadmins = array();
        foreach (explode(',', $CFG->siteadmins) as $admin) {
            $admin = (int)$admin;
            if ($admin) {
                $siteadmins[] = $admin;
            }
        }

        //Exclude users who cannot login
        $nologinusers = $DB->get_fieldset_select('user', 'id', 'auth = "nologin"');

        //Exclude other users (e.g webservice users)
        //Uses profile_field_excludefromuserlists
        $profilefieldusers = array();
        if ($DB->record_exists_select('user_info_field', 'shortname = "excludefromuserlists"')) {
            try {

                $sql = '
                        SELECT
                            u.id
                        FROM
                            {user} u
                                JOIN
                            {user_info_data} uid ON u.id = uid.userid
                                JOIN
                            {user_info_field} uif ON uid.fieldid = uif.id
                        WHERE
                            uif.shortname = :shortname
                                AND uid.data = 1';
                $params['shortname'] = 'excludefromuserlists';
                $records = $DB->get_records_sql($sql, $params);

                foreach ($records as $record) {
                    $profilefieldusers[] = $record->id;
                }

            } catch(Exception $e) {
                error_log($e, DEBUG_NORMAL);
            }
        }

        $excludedusers = array_merge($siteadmins, $nologinusers, $profilefieldusers);

        return $excludedusers;
    }
    
}

