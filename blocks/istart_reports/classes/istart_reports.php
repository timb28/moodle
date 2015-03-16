<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of istart_week
 *
 * @author timbutler
 */
namespace block_istart_reports;

defined('MOODLE_INTERNAL') || die;

define('NUMPASTREPORTDAYS', 6);
define('MANAGERREPORTTYPE', 1);

class istart_week_report {
    
    public  $reporttype,
            $reporttime,
            $course,
            $istartgroups;
    
    public function __construct($reporttype, $course) {
        $this->reporttype = $reporttype;
        $this->reporttime = getdate();
        $this->course = $course;

        $groups = groups_get_all_groups($course->id);

        foreach ($groups as $group) {
            $istartgroup = new istart_group($group);
            if ($istartgroup->isvalidgroup) {
                $this->istartgroups[] = $istartgroup;
            }
        } // foreach

    } // _construct

    /**
    * Sends istart manager reports for a given istart intake group
    * @param stdClass $course The istart course object.
    * @param stdClass $group The group to process.
    * @return TODO true if a report was sent
    */
    function process_manager_reports() {
        // Send out all unsent manager reports from the last NUMPASTREPORTDAYS days.
        // Reports older than NUMPASTREPORTDAYS will not be mailed.  This is to avoid the problem where
        // cron has not been running for a long time or a student moves iStart group,
        // and then suddenly people are flooded with mail from the past few weeks or months
        $daysago = 0;

        foreach ($this->istartgroups as $istartgroup) {

            while ($daysago <= NUMPASTREPORTDAYS) {
                $reporttime = strtotime(date("Ymd")) - (DAYSECS * $daysago);
                $istartweek = $istartgroup->get_istart_week($reporttime);
                error_log("2. Started processing group: ".$istartgroup->group->id." (".$istartgroup->group->name."),  Days ago: $daysago, Report time: $reporttime"); // TODO remove after testing
//                process_manager_report_for_group_on_date($course, $istartgroup->group, $reporttime);
                $daysago++;
            }
        }

        return true;
   }

}

class istart_group {

    public  $group,
            $startdate,
            $istartusers,
            $isvalidgroup;

    public function __construct($group) {
        // Check if the group is valid for istart
        $this->isvalidgroup = false;
        if (!isset($group->idnumber)) {
            $this->isvalidgroup = false;
            error_log("Cannot process iStart manager report for unknown group.");
        } else {
            $date = date_parse($group->idnumber);
            if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"])) {
                // Valid group
                $this->startdate = strtotime($group->idnumber);
                $this->group = $group;
                $this->isvalidgroup = true;
                return;
            } else {
                //Invalid group
                error_log("Cannot process iStart manager report for group: $group->id ($group->name) "
                    . "the group id number '$group->idnumber' is not a valid iStart start date.");
                $this->isvalidgroup = false;
            }
        }
    }

    public function get_istart_week($date) {
        if ($this->isvalidgroup) {
            $istartweek = floor( ($date - $this->startdate) / WEEKSECS);
            error_log("iStart week: " . $istartweek); // TODO remove after testing
            return $istartweek;
        } else {
            return false;
        }
    }
}

class istart_week {

    public  $sectionid,
            $weeknumber,
            $weekname,
            $tasksections;

    public function __construct($courseid, $istartweeknumber){
        global $DB;

        // Get the course section
        try {

            $sql = '
                    SELECT
                        cs.name, cs.section
                    FROM
                        {course_sections} AS cs
                            JOIN
                        {course_format_options} AS cfo ON cs.id = cfo.sectionid
                    WHERE
                        cs.course = :courseid
                            AND cs.visible = 1
                            AND cfo.name = "istartweek"
                            AND cfo.value = :weeknum';
            $params = array(
                            'courseid' => $courseid,
                            'weeknum'  => $istartweeknumber);
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("iStart manager report not sent because the iStart week section cannot be read from the database.");
        }

        $this->sectionid = $record->section;
        $this->weeknumber = $istartweeknumber;
        $this->weekname = $record->name;
    }
}
