<?php

/**
 * Support functions for component 'block_istart_reports', language 'en'
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir. '/coursecatlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/user/selector/lib.php');

use block_istart_reports\istart_group;
use block_istart_reports\istart_week;
use block_istart_reports\istart_week_report;
use block_istart_reports\email\managerreport\managerreport;
use block_istart_reports\email\managerreport\managerwelcome;

define('BLOCK_NAME', 'istart_reports');
define('NUMPASTREPORTDAYS', 6);
define('MANAGERROLESHORTNAME', 'coach');
define('COURSEFORMATOPTIONTYPEFORTASKS', 'reportcompletions');

/**
 * Function to be run periodically according to the scheduled task.
 *
 * Finds all the iStart reports that have yet to be mailed out, and mails them
 * out to all managers as well as other maintance tasks.
 *
 * @return void
 */
function istart_reports_cron() {

    core_php_time_limit::raise(300); // terminate if not able to process cron tasks in 5 minutes

    clean_reports();
    process_manager_reports();

}

/**
 * Cleans manager report queue
 *
 * @return void
 */
function clean_reports() {
    global $DB;

    // Clean store of manager report emails sent and reports processed for past students (sent > 13 weeks ago)
    $oldreportconditions = 'reporttime IS NULL or reporttime < '.(time() - (13 * WEEKSECS));
    $DB->delete_records_select('block_istart_reports', $oldreportconditions);
}

/**
 * Queues manager reports for all intake groups for later sending
 *
 * @return void
 */
function process_manager_reports() {
    
    // Get istart courses that have this block
    $courses = get_courses_with_block(get_blockid(BLOCK_NAME));

    foreach ($courses as $course) {
        // Get the report time as midnight this morning local time
        $reporttime = strtotime(date("Ymd"));

        // Only process courses with group mode activated.
        if (groups_get_course_groupmode($course) == NOGROUPS) {
            continue;
        }

        $istart_week_report = new istart_week_report($course, managerreport::REPORTTYPE, $reporttime);
        $istart_week_report->process_manager_reports();
    }
}

/**
 * Get the block id for this block
 *
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
 *
 * @return array of courses
 */
function get_courses_with_block($blockid) {
    $searchcriteria = array('blocklist' => $blockid);

    return coursecat::search_courses($searchcriteria);
}

/**
 * Gets manager for the given user
 * 
 * @param stdClass $user The user object
 * @return array Manager user objects
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
 *
 * @param int $userid The id of the user
 * @return array Manager user ids
 */
function get_manager_user_ids($userid) {
    global $DB;

    if (empty($userid)) {
        return array();
    }

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
 *
 * @param stdClass $user The user object
 * @param int $managerid The manager's user id
 * @return bool true if successful, false otherwise
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
 *
 * @param int $userid The user's id
 * @param int $managerid The manager's user id
 * @return bool true if successful, false otherwise
 */
function add_manager($user, $manager) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($user->id, MUST_EXIST);
    
    // Assign the new manager (if there is one)
    if (!empty($manager->id)) {
        $success = role_assign($roleid, $manager->id, $context->id);
    }

    // Send the manager the Manager Report welcome email
    $managerwelcome = new \block_istart_reports\email\managerreport\managerwelcome($user, $manager);
    $managerwelcome->send_manager_welcome_to_manager();

    $event = \block_istart_reports\event\manager_added::create(array(
        'context' => $context,
        'objectid' => $user->id,
        'relateduserid' => $manager->id,
    ));
    $event->trigger();

    return isset($success);
}

/**
 * Removes a manager for a user
 *
 * @param int $userid The user's id
 * @param int $managerid The manager's user id
 * @return bool true if success
 */
function remove_manager($userid, $managerid) {
    global $DB;

    $roleid = $DB->get_field('role', 'id', array('shortname'=>MANAGERROLESHORTNAME), IGNORE_MISSING);
    $context = context_user::instance($userid, MUST_EXIST);

    // Assign the new manager (if there is one)
    if (!empty($managerid)) {
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
 * Gets users who should not be selectable in user lists
 * 
 * For example - site administrators, webservice users, users who can't login
 *
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
        $existingmanagerids = array();
        $existingmanagers = get_manager_users($USER);
        if (!empty($existingmanagers)) {
            foreach ($existingmanagers as $manager) {
                $existingmanagers[] = $manager->id;
            }
        }


        // Merge the arrays containing the excluded users
        $excludedusers = array_merge($siteadmins, $nologinusers, $profilefieldusers, $existingmanagerids);

        return $excludedusers;
    }

/**
 * Creates a user_selector form element for iStart students to use to select their manager(s)
 *
 * @author timbutler
 */
class manager_candidate_selector extends user_selector_base {


    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Finds candidate managers
     *
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
        $options['file'] = 'blocks/istart_reports/lib.php';
        $options['extrafields'] = array('city');
        return $options;
    }

    /**
     * Convert a user object to a string suitable for displaying as an option in the list box.
     *
     * Adapted to only display the '()' if there is content to display.
     *
     * @param object $user the user to display.
     * @return string a string representation of the user.
     */
    public function output_user($user) {
        $out = fullname($user);
        if ($this->extrafields) {
            $displayfields = array();
            $hascontent = false;
            foreach ($this->extrafields as $field) {
                $displayfields[] = $user->{$field};

                if (!empty($user->{$field})) {
                    $hascontent = true;
                }
            }
            if ($hascontent) {
                $out .= ' (' . implode(', ', $displayfields) . ')';
            }
        }
        return $out;
    }
}

/**
 * Creates a user_selector form element for iStart students to use to view and remove their current manager(s)
 *
 * @author timbutler
 */
class manager_existing_selector extends user_selector_base {

    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Finds existing managers
     *
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB, $USER;
        
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $manageruserids = get_manager_user_ids($USER->id);

        if (empty($manageruserids)) {
            return array();
        }

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
        $options['file'] = 'blocks/istart_reports/lib.php';
        $options['extrafields'] = array('city');
        return $options;
    }

    /**
     * Convert a user object to a string suitable for displaying as an option in the list box.
     *
     * Adapted to only display the '()' if there is content to display.
     *
     * @param object $user the user to display.
     * @return string a string representation of the user.
     */
    public function output_user($user) {
        $out = fullname($user);
        if ($this->extrafields) {
            $displayfields = array();
            $hascontent = false;
            foreach ($this->extrafields as $field) {
                $displayfields[] = $user->{$field};

                if (!empty($user->{$field})) {
                    $hascontent = true;
                }
            }
            if ($hascontent) {
                $out .= ' (' . implode(', ', $displayfields) . ')';
            }
        }
        return $out;
    }
}