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
            $istartgroups,
            $totalistartweeks;
    
    public function __construct($reporttype, $course) {
        $this->reporttype = $reporttype;
        $this->reporttime = getdate();
        $this->course = $course;

        // Get number of istart weeks
//        $this->totalistartweeks = $this->get_block_config($course->id, BLOCK_NAME, BLOCK_CONFIG_TOTAL_WEEKS);

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
        if ($this->reporttype !== MANAGERREPORTTYPE) {
            return;
        }

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


    /**
     * Get a block configuration variable
     * @param id Course id
     * @param string Block name
     * @param string Configuration variable name
     * @return configuration value
     */
    private function get_block_config($courseid, $name, $configvariable) {
        global $DB;

        $coursecontext = context_course::instance($courseid);
        $blockrecord = $DB->get_record('block_instances', array('blockname' => $name,
            'parentcontextid' => $coursecontext->id), '*', MUST_EXIST);
        $blockinstance = block_instance($name, $blockrecord);

        return $blockinstance->config->{$configvariable};
    }

}
