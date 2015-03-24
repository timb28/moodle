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
 * Support functions for component 'block_istart_reports', language 'en'
 *
 * @package   block_istart_reports
 * @copyright Harcourts Academy <academy@harcourts.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/coursecatlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/selector/lib.php');

use block_istart_reports\istart_group;
use block_istart_reports\istart_week;
use block_istart_reports\istart_week_report;

define('BLOCK_NAME', 'istart_reports');
define('NUMPASTREPORTDAYS', 6);
define('MANAGERREPORTTYPE', 1);
define('MANAGERROLESHORTNAME', 'coach');
define('COURSEFORMATOPTIONTYPEFORTASKS', 'reportcompletions');

/**
 * Function to be run periodically according to the scheduled task.
 *
 * Finds all the iStart reports that have yet to be mailed out, and mails them
 * out to all managers as well as other maintance tasks.
 *
 */
function istart_reports_cron() {

    core_php_time_limit::raise(300); // terminate if not able to process cron tasks in 5 minutes

    clean_reports();
    process_manager_reports();


    return true;
}

/**
 * Cleans manager report queue
 * @return true or error
 */
function clean_reports() {
    global $DB;

    $oldreportconditions = 'reporttime IS NULL or reporttime < '.(time() - YEARSECS);

    // Clean store of manager report emails sent and reports processed for past students (sent > 1 year ago)
    $DB->delete_records_select('block_istart_reports', $oldreportconditions);

    // TODO: Clean store of manager report emails sent for past students no longer enrolled
}

/**
 * Queues manager reports for all intake groups for later sending
 * @return true
 */
function process_manager_reports() {
    
    // TODO: Record in the Moodle event log that istart manager reports have started processing

    // Get istart courses that have this block
    $courses = get_courses_with_block(get_blockid(BLOCK_NAME));

    foreach ($courses as $course) {
        // Get the report time as midnight this morning local time
        $reporttime = strtotime(date("Ymd"));

        error_log("1. Started processing reports for course: $course->id at time: " . date("Y-m-d", $reporttime)); // TODO remove after testing

        // Only process courses with group mode activated.
        if (groups_get_course_groupmode($course) == NOGROUPS) {
            error_log(" - Course skipped: No groups");
            continue;
        }

        $istart_week_report = new istart_week_report($course, MANAGERREPORTTYPE, $reporttime);
        $istart_week_report->process_manager_reports();
    }

    return true;
}

/**
 * Get the block id for this block
 * @return int Block ID
 */
function get_blockid($name) {
    global $DB;
    
    $block = $DB->get_record('block', array('name'=>$name));
    if (isset($block)) {
        return $block->id;
    } else {
        // this block can't be found in the database
        throw new moodle_exception('noblockid', 'block_istart_reports');
    }
}

/**
 * Gets all courses that contain this block
 * @return array courses[]
 */
function get_courses_with_block($blockid) {
    $searchcriteria = array('blocklist' => $blockid);

    return coursecat::search_courses($searchcriteria);
}

/**
 * Gets manager's email address for the given user
 * @param stdClass $user The user object
 * @return string manager's email address
 */
function get_manager_email_address($user) {
    profile_load_data($user);
    return clean_text($user->profile_field_manageremailaddress);
}

/**
 * Gets manager for the given user
 * @param stdClass $user The user object
 * @return array manager user objects
 */
function get_manager_users($user) {
    global $DB;

    $managerusers = null;

    $context = context_user::instance($user->id);
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);

    if (!isset($roleid)) {
        return false;
    }

    $userfields = 'u.id, u.username, u.email, u.mailformat, ' . get_all_user_name_fields(true, 'u');
    $roleusers = get_role_users($roleid, $context, false, $userfields);
    if (!empty($roleusers)) {
        $strroleusers = array();
        foreach ($roleusers as $user) {
            $managerusers[] = $user;
        }
    }

    return $managerusers;
}

/**
 * Gets manager for the given user
 * @param stdClass $user The user object
 * @return array manager user objects
 */
function get_manager_user_ids($userid) {
    global $DB;

    $managerusers = null;
    $usercontext = context_user::instance($userid);
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);

    if (!isset($roleid)) {
        return false;
    }

    $roleusers = get_role_users($roleid, $usercontext, false, 'u.id');
    if (!empty($roleusers)) {
        foreach ($roleusers as $user) {
            $managerusers[] = $user->id;
        }
    }

    $manageruserids = '0';
    if (isset($managerusers)) {
        $manageruserids = implode(",", $managerusers);
    }

    return $manageruserids;
}

/**
 * Sets manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function set_manager($user, $managerid) {
    global $DB;
    
    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($user->id, MUST_EXIST);

    // Unassign the old managers
    $existingmanagers = get_manager_users($user);
    if (isset($existingmanagers)) {
        foreach ($existingmanagers as $existingmanager) {
            $unassign = role_unassign($roleid, $existingmanager->id, $context->id);
        }
    }

    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_assign($roleid, $managerid, $context->id);
    }
    return isset($success);
}

/**
 * Adds manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function add_manager($userid, $managerid) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($userid, MUST_EXIST);
    
    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_assign($roleid, $managerid, $context->id);
    }

    $event = \block_istart_reports\event\manager_added::create(array(
        'context' => $context,
        'objectid' => $userid,
        'relateduserid' => $managerid,
    ));
    $event->trigger();

    return isset($success);
}

/**
 * Removes a manager for a user
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function remove_manager($userid, $managerid) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($userid, MUST_EXIST);

    // Assign the new manager (if there is one)
    if (isset($managerid) && $managerid != '') {
        $success = role_unassign($roleid, $managerid, $context->id);
    }

    $event = \block_istart_reports\event\manager_removed::create(array(
        'context' => $context,
        'objectid' => $userid,
        'relateduserid' => $managerid,
    ));
    $event->trigger();

    return isset($success);
}

/**
 * Sets manager's email address for a user
 * @param stdClass $user The user object
 * @param string $emailaddress The manager's email address to save
 * @return bool true if success
 */
function set_manager_email_address($user, $emailaddress) {
    profile_load_data($user);
    if (strlen($emailaddress) == 0) {
        $user->profile_field_manageremailaddress = ""; // Avoids it being saved as "0"
    } else {
        $user->profile_field_manageremailaddress = substr($emailaddress, 0, 255);
    }
    profile_save_data($user);
    return true;
}

/**
 * Gets users who should not appear in the candidate manager list
 * @return array user ids of excluded users
 */
function get_excluded_users() {
        global $DB, $CFG, $USER;

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

        // Exclude current managers
        $existingmanagers = array();
        foreach (get_manager_users($USER) as $manager) {
            $existingmanagers[] = $manager->id;
        }


        // Merge the arrays containing the excluded users
        $excludedusers = array_merge($siteadmins, $nologinusers, $profilefieldusers, $existingmanagers);

        return $excludedusers;
    }

/**
 * Description of manager_selector
 *
 * @author timbutler
 */
class manager_candidate_selector extends user_selector_base {


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
            $groupname = get_string('candidatemanagersmatching', 'block_istart_reports', $search);
        } else {
            $groupname = get_string('candidatemanagers', 'block_istart_reports');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'blocks/istart_reports/managers.php';
        return $options;
    }
}

/**
 * Description of manager_selector
 *
 * @author timbutler
 */
class manager_existing_selector extends user_selector_base {
    protected $userid;

    public function __construct($name, $options) {
        $this->userid = $options['userid'];
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

        $manageruserids = get_manager_user_ids($this->userid);

        if ($wherecondition) {
            $wherecondition = "$wherecondition AND id IN ($manageruserids)";
        } else {
            $wherecondition = "id IN ($manageruserids)";
        }

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
            $groupname = get_string('existingmanagersmatching', 'block_istart_reports', $search);
        } else {
            $groupname = get_string('existingmanagers', 'block_istart_reports');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['file'] = 'blocks/istart_reports/managers.php';
        return $options;
    }
}