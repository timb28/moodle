<?php

/**
 * iStart group class that contains information about the group
 * and it's reports, users and last report istart week
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_istart_reports;

class istart_group {

    public  $group,
            $isvalidgroup,
            $reportdate,
            $reportweek,
            $startdate,
            $istartusers,
            $istartweek;

    /**
     * Constructs the istart_group for a given group.
     *
     * @param stdClass $group The group object.
     */
    public function __construct($group) {
        $this->group = $group;

        $this->validate_group();
        $this->setup_start_date();
        $this->setup_report_week_num();

        $reportsendtime     = $this->startdate + ($this->reportweek * WEEKSECS) + DAYSECS;
        $this->reportdate   = date("Ymd", $reportsendtime);
    }

    /**
     * Checks if an iStart group is configured correctly
     * @return bool true if successful, false if not
     */
    private function validate_group() {
        $this->isvalidgroup = false;

        try {
            $date = date_parse($this->group->idnumber);
            if ($date["error_count"] == 0 &&
                    checkdate($date["month"], $date["day"], $date["year"])) {
                $this->isvalidgroup = true;
            }
        } catch (Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }

        return true;
    }

    /**
     * Calculates and sets the current istart startdate timestamp
     * @return void
     */
    private function setup_start_date() {
        // Check if the group is valid for istart
        if ($this->isvalidgroup === true) {
            $this->startdate = strtotime($this->group->idnumber);
        }
    }

    /**
     * Calculates and sets the current istart weeknumber the group is in
     * @param int $courseid The course id
     * @return bool true if successful, false if not
     */
    private function setup_report_week_num() {
        if ($this->isvalidgroup === true && isset($this->startdate)) {
            $this->reportweek = floor( (time() - $this->startdate) / WEEKSECS);
        }
    }

    /**
     * Creates istart_user objects for group users. Called only when a report is due
     * @return bool True on succcess, false otherwise
     */
    private function setup_group_users() {
        $groupmembers = groups_get_members($this->group->id);

        if (empty($groupmembers)) {
            $this->isvalidgroup = false;
            return false;
        }

        foreach ($groupmembers as $user) {
            $this->istartusers[] = new istart_user($user, $this->istartweek);
        }

        return true;
    }

    /**
     * Sets-up the istart week and group users
     * Called when preparing to send a report for the group
     * @return bool true if successful, false if not
     */
    public function prepare_for_group_report() {
        try {
            // Get all week tasks
            $this->istartweek = new istart_week($this->group->courseid, $this->reportweek);

            // Get all group users
            $this->setup_group_users(); // Must be done after getting all the week tasks

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }
        return true;
    }

}
