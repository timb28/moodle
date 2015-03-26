<?php

namespace block_istart_reports;

/**
 * iStart User Tasks containing the following information about a students
 * completion of a task section:
 *  - Section ID
 *  - Number of tasks complete
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class istart_user_tasks {
    public  $sectionid,
            $numtaskscomplete;

    /**
     * Constructs the istart_user_tasks for a given course section
     *
     * @param int $sectionid The id of the course section.
     * @param int $numtaskscomplete The number of tasks the student has completed in that section.
     */
    public function __construct($sectionid, $numtaskscomplete) {
        $this->sectionid = $sectionid;
        $this->numtaskscomplete = $numtaskscomplete;
    }
}
