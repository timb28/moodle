<?php

class block_istart_reports_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blockconfigheader', 'block_istart_reports'));

        $istartweekoptions  = array();
        $ministartweeks     = 1;
        $maxistartweeks     = 52;
        $defaultistartweeks = 24;

        for ($i=$ministartweeks; $i <= $maxistartweeks; $i++) {
            $istartweekoptions[$i] = $i;
        }

        $mform->addElement('select', 'config_totalistartweeks', get_string('blockconfigistartweeks', 'block_istart_reports'), $istartweekoptions);
        $mform->setDefault('config_totalistartweeks', $defaultistartweeks);
//
//        $mform->addElement('html', '<p>'.get_string('managerconfigdesc', 'block_istart_reports').'</p>');
//

    }
}
