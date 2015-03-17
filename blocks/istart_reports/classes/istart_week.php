<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace block_istart_reports;

/**
 * Description of istart_week
 *
 * @author timbutler
 */
class istart_week {

    public  $sectionid,
            $weeknumber,
            $weekname,
            $tasksections;

    public function __construct($courseid, $weeknumber){
        global $DB;

        $this->weeknumber = $weeknumber;

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
                            'weeknum'  => $weeknumber);
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("iStart manager report not sent because the iStart week section cannot be read from the database.");
        }

        $this->sectionid = $record->section;
        $this->weekname = $record->name;
    }
}
