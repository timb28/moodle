<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace block_istart_reports;

/**
 * Description of istart_user
 *
 * @author timbutler
 */
class istart_user {
    public  $user,
            $istartweek,
            $usertasks,
            $sentto,
            $senttime,
            $managers;

    public function __construct($user, $istartweek) {
        $this->user         = $user;
        $this->istartweek   = $istartweek;
        
        $this->setup_managers();
        $this->setup_user_tasks();
    }

    /**
     * Gets the number of tasks a user has completed in the istart week
     * @return null
     */
    public function setup_user_tasks() {
        global $DB;

        if (!isset($this->istartweek->tasksections)) {
            return false;
        }

        try {

            $tasksectionids = null;

            // Get the section ids for the task sections of the istart week
            foreach ($this->istartweek->tasksections as $tasksection) {
                $tasksectionids[] = $tasksection->sectionid;
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
                        cmc.userid = :userid AND cm.section $insql
                    GROUP BY
                        cm.section";
            $params['userid'] = $this->user->id;
            $taskscomplete = $DB->get_records_sql($sql, $params);

            foreach ($taskscomplete as $section) {
                $this->usertasks[$section->sectionid] = new istart_user_tasks($section->sectionid, $section->numtaskscomplete);
            }

            error_log("     - tasks complete objects user ". $this->user->firstname .": " . print_r($this->usertasks, 1));

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("Could not obtain istart tasks complete.");
        }

//        return $taskscomplete->total;
    }

    private function setup_managers() {
        $existingmanagers = get_manager_users($this->user);
        foreach ($existingmanagers as $manager) {
            $this->managers[] = $manager;
        }
    }
}
