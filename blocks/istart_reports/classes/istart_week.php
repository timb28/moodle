<?php

namespace block_istart_reports;

/**
 * iStart Week containing the following information about an iStart week:
 *  - Week number
 *  - Week name
 *  - Subsections that contain tasks
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class istart_week {

    public  $courseid,
            $sectionid,
            $sectionnumber,
            $weeknumber,
            $weekname,
            $tasksections;

    /**
     * Constructs the istart_week for a given course and week.
     *
     * @param int $courseid The course id.
     * @param int $weeknumber The iStart week number
     */
    public function __construct($courseid, $weeknumber){
        global $DB;

        $this->courseid     = $courseid;
        $this->weeknumber   = $weeknumber;

        // Get the course section. Note: the lowest cs.section value is the week main section
        try {

            $sql = '
                    SELECT
                        cs.id as sectionid, cs.section as sectionnumber, cs.name
                    FROM
                        {course_sections} AS cs
                            JOIN
                        {course_format_options} AS cfo ON cs.id = cfo.sectionid
                    WHERE
                        course = :courseid AND cfo.name = "istartweek"
                            AND cfo.value = :weeknumber
                            order by cs.section
                            limit 0,1;';
            $params = array(
                            'courseid' => $courseid,
                            'weeknumber'  => $weeknumber);
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("iStart manager report not sent because the iStart week section cannot be read from the database.");
        }

        $this->sectionid        = $record->sectionid;
        $this->sectionnumber    = $record->sectionnumber;
        $this->weekname         = $record->name;

        $this->setup_task_sections();
    }

    /**
     * Constructs the array of tasksections for this iStart week.
     *
     * @return bool True if successful, false otherwise
     */
    private function setup_task_sections() {
        global $DB;

        // Get all course sections for the istart week

        try {

            $sql = '
                    SELECT
                        cs.id as sectionid, cs.section as sectionnumber, cs.name
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
                            'courseid' => $this->courseid,
                            'weeknum'  => $this->weeknumber);
            $records = $DB->get_records_sql($sql, $params);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }

        foreach ($records as $record) {
            // Ignore the parent istart week section.
            if ($record->sectionid == $this->sectionid) {
                continue;
            }

            $tasksection = new istart_task_section($this->courseid, $record->sectionid, $record->sectionnumber, $record->name);
            $this->tasksections[] = $tasksection;
        }

        return true;
    }
}
