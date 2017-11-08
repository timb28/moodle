<?php

namespace block_istart_reports;

/**
 * iStart User containing information about the User's:
 *  - Weekly tasks
 *  - Report sent to
 *  - Report sent time
 *  - Manager(s)
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class istart_user {
    public  $user,
            $istartweek,
            $usertasks,
            $managers;

    /**
     * Constructs the istart_user for the given user and week.
     *
     * @param stdClass $user The user object.
     * @param stdClass $istartweek The iStart week object
     */
    public function __construct($user, istart_week $istartweek) {
        $this->user         = $user;
        $this->istartweek   = $istartweek;
        
        $this->setup_managers();
        $this->setup_user_tasks();
    }

    /**
     * Gets the number of tasks a user has completed in the istart week
     * from the database.
     * 
     * @return bool true if successful, false otherwise
     */
    private function setup_user_tasks() {
        global $DB;

        if (!isset($this->istartweek->tasksections)) {
            return false;
        }

        try {

            $tasksectionids = null;

            // Get the section ids for the task sections of the istart week
            foreach ($this->istartweek->tasksections as $tasksection) {
                $tasksectionids[] = $tasksection->sectionid;
                $this->usertasks[$tasksection->sectionid] = new istart_user_tasks($tasksection->sectionid, 0);
            }

            list($insql, $params) = $DB->get_in_or_equal($tasksectionids, SQL_PARAMS_NAMED);

            $sql = "
                    SELECT
                        cm.section as 'sectionid', COUNT(cm.id) as 'numtaskscomplete'
                    FROM
                        {course_modules} cm
                            JOIN
                        {label} l ON l.id = cm.instance
                            JOIN
                        {course_modules_completion} cmc ON cmc.coursemoduleid = cm.id
                    WHERE
                        cmc.completionstate = 1 AND cmc.userid = :userid AND cm.section $insql
                    GROUP BY
                        cm.section";
            $params['userid'] = $this->user->id;
            $taskscomplete = $DB->get_records_sql($sql, $params);

            foreach ($taskscomplete as $section) {
                $this->usertasks[$section->sectionid]->numtaskscomplete = $section->numtaskscomplete;
            }

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("Could not obtain istart tasks complete.");
        }

        return true;
    }

    /**
     * Gets the user's managers.
     *
     * @return array Array of manager user objects
     */
    private function setup_managers() {
        $existingmanagers = get_manager_users($this->user);
        if (empty($existingmanagers)) {
            return false;
        }

        foreach ($existingmanagers as $manager) {
            $this->managers[] = $manager;
        }

        return true;
    }

    /**
     * Gets the number of tasks a user has completed for a given section id.
     * 
     * @return int Number of tasks completed or -1 if no user tasks
     *             have been set
     */
    public function get_num_tasks_complete($sectionid) {
        $numtaskscomplete = -1;
        if (isset($this->usertasks)) {
            $usertasks = $this->usertasks[$sectionid];
            $numtaskscomplete = $usertasks->numtaskscomplete;
        }
        return $numtaskscomplete;
    }
}
