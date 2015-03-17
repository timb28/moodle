<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace block_istart_reports;

/**
 * Description of istart_group
 *
 * @author timbutler
 */
class istart_group {

    public  $group,
            $isvalidgroup,
            $reportsendday,
            $reportweeknum,
            $startdate,
            $istartusers,
            $istartweek;

    public function __construct($group) {
        $this->group = $group;
        $this->validate_group();
        $this->setup_start_date();
        $this->setup_report_week_num();
    }

   private function validate_group() {
        $this->isvalidgroup = false;

        try {
            $date = date_parse($this->group->idnumber);
            if ($date["error_count"] == 0 &&
                    checkdate($date["month"], $date["day"], $date["year"])) {
                // Valid group
                $this->isvalidgroup = true;
                return true;
            }
        } catch (Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return "Cannot process iStart report - iStart group is invalid.";
        }
    }

    private function setup_start_date() {
        // Check if the group is valid for istart
        if ($this->isvalidgroup === true) {
            $this->startdate = strtotime($this->group->idnumber);
        }
    }

    private function setup_report_week_num() {
        if ($this->isvalidgroup === true && isset($this->startdate)) {
            $this->reportweeknum = floor( (time() - $this->startdate) / WEEKSECS);
        }
    }

    /**
     * Creates istart_user objects for group users. Called only when a report is due
     * @return true or error
     */
    public function setup_group_users() {
        $hasusers = false;

        $groupmembers = groups_get_members($this->group->id);

        foreach ($groupmembers as $user) {
            $this->istartusers[] = new istart_user($user);
            $hasusers = true;
        }

        return $hasusers;
    }
    
    public function setup_istart_week($courseid, $weeknum) {
        error_log('    - setting up istart week: ' . $weeknum);
        $this->istartweek = new istart_week($courseid, $weeknum);
        return true;
    }

}
