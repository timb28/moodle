<?php
/**
 * iStart Reports block
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_istart_reports;

require_once($CFG->dirroot . '/blocks/istart_reports/lib.php');

use block_istart_reports\email\managerreport\managerreport;
use block_istart_reports\email\managerreport\managerreport_text;
use block_istart_reports\email\managerreport\managerreport_html;

class istart_week_report {
    
    public  $reporttype,
            $reporttime,
            $course,
            $totalweeks,
            $istartgroups;
    
    /**
     * Constructs the istart_week_report for a given course, report type and time.
     *
     * @param int $course The course object.
     * @param int $reporttype The iStart report type.
     * @param int $reporttime The timestamp of when the report is for.
     */
    public function __construct($course, $reporttype, $reporttime) {
        $this->reporttype = $reporttype;
        $this->reporttime = $reporttime;
        $this->course = $course;
        $this->setup_totalweeks();
        $this->setup_istartgroups();

    } // _construct

    /**
     * Calculates then stores the total number of istart weeks available
     *
     * @return bool true if successful, false if not
     */
    private function setup_totalweeks() {
        global $DB;

        $course = $this->course;

        if (empty($course)) {
            return false;
        }

        // Get total number of istart weeks
        try {

            $sql = '
                    SELECT
                        MAX(CAST(cfo.value AS UNSIGNED)) as totalweeks
                    FROM
                        {course_format_options} AS cfo
                    WHERE
                        cfo.courseid = :courseid
                            AND cfo.name = :format_option_name';
            $params = array(
                            'courseid' => $course->id,
                            'format_option_name'  => 'istartweek');
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }

        $this->totalweeks = $record->totalweeks;
        return true;
    }

    /**
     * Finds then stores the istart groups
     *
     * @return bool true if successful, false if not
     */
    private function setup_istartgroups() {
        if (empty($this->reporttime)) {
            return false;
        }

        $course = $this->course;

        if (empty($course)) {
            return false;
        }

        $allgroups = groups_get_all_groups($course->id);

        foreach ($allgroups as $group) {
            $istartgroup = new istart_group($group, $this->reporttime);
            if ($istartgroup->isvalidgroup) {
                $this->istartgroups[] = $istartgroup;
            }
        }

        return true;
    }

    /**
     * Processes istart manager reports for a given istart intake group
     *
     * @return bool true if commpleted successfully
     */
    public function process_manager_reports() {
        if ($this->reporttype !== managerreport::REPORTTYPE) {
            return false;
        }

        // Send out manager report for groups that finished an istart week yesterday
        foreach ($this->istartgroups as $istartgroup) {

            // Skip groups who have finished all iStart weeks +1 week
            if ($istartgroup->reportweek > $this->totalweeks) {
                error_log(" - 2. Skipping group who have completed iStart: ".$istartgroup->group->id.
                        " (".$istartgroup->group->name.") iStart week: " . $istartgroup->reportweek);
                continue;
            }

            // TODO remove testing code below
            error_log(" - 2. Started processing group: ".$istartgroup->group->id." (".$istartgroup->group->name.") iStart report week: " . $istartgroup->reportweek);
            error_log("   - group start date: " . date("Y-m-d", $istartgroup->startdate));
            error_log("   - group report week: " . $istartgroup->reportweek);

            $reportsendtime = $istartgroup->startdate + ($istartgroup->reportweek * WEEKSECS) + DAYSECS;

            error_log("   - group report send date: " . date("Y-m-d", $reportsendtime));

            if (date("Ymd", $reportsendtime) == date("Ymd", $this->reporttime)) {
                error_log(" - 3. Sending report today");

                // Get all group users
                $istartgroup->prepare_for_group_report();

                // Skip to next group if this group is invalid
                if (!$istartgroup->isvalidgroup) {
                    continue;
                }

                error_log(print_r($istartgroup->istartweek, 1));

                // Check if reports for those users have been sent
                $this->prepare_manager_report_for_group($istartgroup);
            }

        }

        return true;
   }

    /**
     * Prepares to send manager reports for group members.
     *
     * @param stdClass $istartgroup The iStart group.
     * @return bool True if successful, false otherwise.
     */
    private function prepare_manager_report_for_group(istart_group $istartgroup) {
        $istartusers = $istartgroup->istartusers;

        if (empty($istartgroup || empty($istartusers) || !$istartgroup->isvalidgroup)) {
            return false;
        }

        foreach ($istartusers as $istartuser) {
            $user    = $istartuser->user;
            error_log(" - Preparing to send manager report for $user->id at $this->reporttime"); // TODO remove after testing

            // Check if already sent
            if (!$this->is_report_sent($istartgroup, $user, managerreport::REPORTTYPE)) {
                $this->prepare_manager_report_for_user($istartgroup, $istartuser);
            } else {
                error_log(" - Manager report for $user->id at $this->reporttime already sent"); // TODO remove after testing
            }
        }

        return true;
    }

    /**
     * Prepares to send a manager report for an iStart user
     *
     * @param stdClass $istartgroup The iStart group
     * @param stdClass $istartuser The iStart user
     * @return bool true if successful, false otherwise
     */
    private function prepare_manager_report_for_user($istartgroup, $istartuser) {
        // Get all the user's managers
        $managers = $istartuser->managers;

        if(empty($managers)) {
            // 'No managers for user: $user->id ($user->firstname $user->lastname).';
            return false;
        }

        foreach ($managers as $manager) {
            // Does the user have a manager's email address set?
            $manageremailaddress = $manager->email;
            if ($manageremailaddress == NULL) {
                // error_log('Manager email is not set for user: $istartuser->id ($istartuser->firstname $istartuser->lastname).');
                continue;
            }

            // Is the manager's email address valid?
            if (!validate_email($manageremailaddress)) {
                // error_log('Manager email ($manageremailaddress) not valid for user:' . ' $istartuser->id ($istartuser->firstname $istartuser->lastname).');
                continue;
            }

            $managerreport = new managerreport($this->course, $istartgroup, $istartuser, $manager, $this->reporttime);
            $managerreport->send_manager_report_to_manager();
        }

        return true;
    }

    /**
     * Checks if an iStart report has been sent previously.
     *
     * @param stdClass $istartgroup The course group
     * @param stdClass $user The user
     * @param int $reporttype The iStart report type
     * @return bool true if the report has already been sent, false otherwise
     */
    private function is_report_sent (istart_group $istartgroup, $user, $reporttype) {
        global $DB;

        $group = $istartgroup->group;

        try {
            $reportsent = $DB->record_exists_select('block_istart_reports',
                    'courseid = :courseid AND groupid = :groupid AND userid = :userid'
                    . ' AND reporttype = :reporttype AND reportweek = :reportweek AND senttime IS NOT NULL',
                         array(
                            'courseid'      => $group->courseid,
                            'groupid'       => $group->id,
                            'userid'        => $user->id,
                            'reporttype'    => $reporttype,
                            'reportweek'    => $istartgroup->reportweek) );
        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            $reportsent = false;
        }

        return $reportsent;
    }

}
