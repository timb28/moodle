<?php

namespace block_istart_reports;

/**
 * iStart Task Section containing information about:
 *  - The course that contains it
 *  - The section's properties (id, number, name)
 *  - The total number of tasks in the section
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class istart_task_section {

    public  $courseid,
            $sectionid,
            $sectionnumber,
            $sectionname,
            $numtasks;

    /**
     * Constructs the istart_task_section for the given course and section.
     *
     * @param int $courseid The course id.
     * @param int $sectionid The course section id.
     * @param int $sectionnumber The course section number.
     * @param string $sectionname The name of the course section.
     */
    public function __construct($courseid, $sectionid, $sectionnumber, $sectionname) {
        $this->courseid         = $courseid;
        $this->sectionid        = $sectionid;
        $this->sectionnumber    = $sectionnumber;
        $this->sectionname      = $sectionname;

        $this->setup_total_tasks();
    }

    /**
     * Sets-up the total number of task for an iStart week task section.
     *
     * @return bool True if successful, false otherwise
     */
    private function setup_total_tasks() {
        global $DB;

        if (empty($this->courseid) || empty($this->sectionid)) {
            return false;
        }

        // Get all course sections that contain tasks
        try {

            $sql = '
                    SELECT
                        COUNT(id)
                    FROM
                        {course_modules}
                    WHERE
                        course = :courseid AND completion > 0
                            AND section = :sectionid';
            $params = array(
                            'courseid' => $this->courseid,
                            'sectionid'  => $this->sectionid);
            $totaltasks = $DB->count_records_sql($sql, $params);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return false;
        }

        $this->numtasks = $totaltasks;

        return true;
    }
}
