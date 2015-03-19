<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace block_istart_reports;

require_once($CFG->dirroot . '/blocks/istart_reports/lib.php');

/**
 * Description of istart_week
 *
 * @author timbutler
 */
class istart_week_report {
    
    public  $reporttype,
            $reporttime,
            $course,
            $totalweeks,
            $istartgroups,
            $istartweeks;
    
    public function __construct($course, $reporttype, $reporttime) {
        $this->reporttype = $reporttype;
        $this->reporttime = $reporttime;
        $this->course = $course;
        $this->setup_totalweeks($course->id);
        $this->setup_istartgroups($course->id);

    } // _construct

    private function setup_totalweeks($courseid) {
        global $DB;

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
                            'courseid' => $courseid,
                            'format_option_name'  => 'istartweek');
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("iStart manager report not sent because the total iStart weeks cannot be read from the database.");
        }

        $this->totalweeks = $record->totalweeks;
    }

    private function setup_istartgroups($courseid) {
        if (!isset($this->reporttime)) {
            return;
        }

        $allgroups = groups_get_all_groups($courseid);

        foreach ($allgroups as $group) {
            $istartgroup = new istart_group($group, $this->reporttime);
            if ($istartgroup->isvalidgroup) {
                $this->istartgroups[] = $istartgroup;
            }
        }
    }

    /**
    * Sends istart manager reports for a given istart intake group
    * @param stdClass $course The istart course object.
    * @param stdClass $group The group to process.
    * @return TODO true if a report was sent
    */
    public function process_manager_reports() {
        if ($this->reporttype !== MANAGERREPORTTYPE) {
            return;
        }

        // Send out all unsent manager reports from the last NUMPASTREPORTDAYS days.
        // Reports older than NUMPASTREPORTDAYS will not be mailed.  This is to avoid the problem where
        // cron has not been running for a long time or a student moves iStart group,
        // and then suddenly people are flooded with mail from the past few weeks or months
        foreach ($this->istartgroups as $istartgroup) {

            // Skip groups who have finished iStart
            if ($istartgroup->reportweeknum > $this->totalweeks) {
                error_log(" - 2. Skipping group who have completed iStart: ".$istartgroup->group->id.
                        " (".$istartgroup->group->name.") iStart week: " . $istartgroup->reportweeknum);
                continue;
            }

            // TODO remove testing code below
            error_log(" - 2. Started processing group: ".$istartgroup->group->id." (".$istartgroup->group->name.") iStart report week: " . $istartgroup->reportweeknum);
            error_log("   - group start date: " . date("Y-m-d", $istartgroup->startdate));
            error_log("   - group report week: " . $istartgroup->reportweeknum);

            $reportsendtime = $istartgroup->startdate + ($istartgroup->reportweeknum * WEEKSECS) + DAYSECS;

            error_log("   - group report send date: " . date("Y-m-d", $reportsendtime));

            if (date("Ymd", $reportsendtime) == date("Ymd", $this->reporttime)) {
                error_log(" - 3. Sending report today");

                // Get all group users
                $istartgroup->prepare_for_group_report();

                error_log(print_r($istartgroup->istartweek, 1));

                // Check if reports for those users have been sent
                $this->send_manager_report_to_group($istartgroup);
            }

        }

        return true;
   }

   /**
    * Sends an istart user a manager report for a given date.
    * @param int $courseid course ID of the istart course.
    * @param int $groupid ID of the user's group.
    * @param stdClass $user user being reported on.
    * @param string $istartweek The istart week.
    * @return true or error
    */
   function send_manager_report_to_group($istartgroup) {
        global $CFG, $DB;

        $istartusers = $istartgroup->istartusers;

        foreach ($istartusers as $istartuser) {
           $user    = $istartuser->user;
           $group   = $istartgroup->group;
           error_log(" - Sending manager report for $user->id at $this->reporttime"); // TODO remove after testing

            // Check if already sent
            if (!$this->is_report_sent($group, $user, MANAGERREPORTTYPE, $this->reporttime)) {
                $this->send_manager_report_for_user($istartgroup, $istartuser);
            }



       }
    }
   
    private function is_report_sent ($group, $user, $reporttype, $reporttime) {
        global $DB;

        $reportsent = false;

        try {
            $reportsent = $DB->record_exists_select('block_istart_reports',
                    'courseid = :courseid AND groupid = :groupid AND userid = :userid'
                    . ' AND reporttype = :reporttype AND reporttime = :reporttime AND senttime IS NOT NULL',
                         array(
                            'courseid' => $group->courseid,
                            'groupid'  => $group->id,
                            'userid'   => $user->id,
                            'reporttype' => $reporttype,
                            'reporttime' => $reporttime) );
        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
        }

        return $reportsent;
   }

    private function send_manager_report_for_user($istartgroup, $istartuser) {
        // Get all the user's managers
        $managers = $istartuser->managers;

        if(!isset($managers)) {
            return 'No managers for user: $user->id ($user->firstname $user->lastname).';
        }

        foreach ($managers as $manager) {
            // Does the user have a manager's email address set?
            $manageremailaddress = $manager->email;
            if ($manageremailaddress == NULL) {
                $user = $istartuser->user;
                return 'Manager email is not set for user: $user->id ($user->firstname $user->lastname).';
            }

            // Is the manager's email address valid?
            if (!validate_email($manageremailaddress)) {
                $user = $istartuser->user;
                return 'Manager email ($manageremailaddress) not valid for user:'
                        . ' $user->id ($user->firstname $user->lastname).';
            }
        }


    }

}
