<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configcheckbox('filter_flowplayer_enable_mp4', get_string('mediapluginmp4','filter_flowplayer'), '', 1));
    $settings->add(new admin_setting_configcheckbox('filter_flowplayer_enable_mp3', get_string('mediapluginmp3','filter_flowplayer'), '', 1));
    
}
