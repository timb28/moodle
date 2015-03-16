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
            $startdate,
            $istartusers,
            $isvalidgroup;

    public function __construct($group) {
        $this->group = $group;
        $this->setup_start_date();
        
    }

    private function setup_start_date() {
        // Check if the group is valid for istart
        if ($this->validate_group() === true) {
            $this->startdate = strtotime($this->group->idnumber);
        }
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
