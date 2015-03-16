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
        // Check if the group is valid for istart
        $this->isvalidgroup = false;
        if (!isset($group->idnumber)) {
            $this->isvalidgroup = false;
            error_log("Cannot process iStart manager report for unknown group.");
        } else {
            $date = date_parse($group->idnumber);
            if ($date["error_count"] == 0 && checkdate($date["month"], $date["day"], $date["year"])) {
                // Valid group
                $this->startdate = strtotime($group->idnumber);
                $this->group = $group;
                $this->isvalidgroup = true;
                return;
            } else {
                //Invalid group
                error_log("Cannot process iStart manager report for group: $group->id ($group->name) "
                    . "the group id number '$group->idnumber' is not a valid iStart start date.");
                $this->isvalidgroup = false;
            }
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
