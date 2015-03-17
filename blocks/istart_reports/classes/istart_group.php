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
            $reportweek,
            $startdate,
            $istartusers,
            $istartweek;

    public function __construct($group) {
        $this->group = $group;
        $this->validate_group();
        $this->setup_start_date();
        $this->setup_report_week();
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

    private function setup_istartweek($courseid) {

        for ($i=1; $i<=$this->totalweeks; $i++) {
            error_log('setting up istart week: ' . $i);
            $this->istartweeks[] = new istart_week($courseid, $i);
        }

        error_log(print_r($this->istartweeks));

        return true;
    }

    private function setup_report_week() {
        if ($this->isvalidgroup === true && isset($this->startdate)) {
            $this->reportweek = floor( (time() - $this->startdate) / WEEKSECS);
        }
    }

}
