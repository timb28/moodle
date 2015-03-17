<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace block_istart_reports;

/**
 * Description of istart_task_section
 *
 * @author timbutler
 */
class istart_task_section {

    public  $courseid,
            $sectionid,
            $sectionnumber,
            $sectionname,
            $numtasks;

    public function __construct($courseid, $sectionid, $sectionnumber, $sectionname) {
        $this->courseid         = $courseid;
        $this->sectionid        = $sectionid;
        $this->sectionnumber    = $sectionnumber;
        $this->sectionname      = $sectionname;

        $this->setup_total_tasks();
    }

    private function setup_total_tasks() {
        global $DB;

        // Get all course sections that contain tasks
        try {

            $table = 'course_modules';
            $conditions = array(
                            'course' => $this->courseid,
                            'completion' => 1,
                            'section'  => $this->sectionid);
            $totaltasks = $DB->count_records($table, $conditions);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("Could not obtain iStart total tasks in course: $this->courseid "
                    . "section: $this->sectionid because the database could not be read.");
        }

        $this->numtasks = $totaltasks;
    }
}
