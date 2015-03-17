<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace block_istart_reports;

/**
 * Description of istart_task_section
 *
 * @author timbutler
 */
class istart_task_section {

    public  $sectionid,
            $sectionname,
            $totaltasks;

    public function __construct($sectionid, $sectionname) {
        $this->sectionid    = $sectionid;
        $this->sectionname  = $sectionname;

        // TODO get total tasks from DB
    }
}
