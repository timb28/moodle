<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace block_istart_reports;

/**
 * Description of istart_user_tasks
 *
 * @author timbutler
 */
class istart_user_tasks {
    public  $sectionid,
            $sectionname,
            $numtaskscomplete;

    public function __construct($sectionid, $sectionname) {
        $this->sectionid = $sectionid;
        $this->sectionname = $sectionname;
    }
}
