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
            $usertasks,
            $sentto,
            $senttime;

    public function __construct($user) {
        $this->user = $user;
    }
}
