<?php

class block_istart_reports_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $CFG;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blockconfigheader', 'block_istart_reports'));

        $totalweekoptions  = array();
        $mintotalweeks     = 1;
        $maxtotalweeks     = 52;
        $defaulttotalweeks = 24;

        for ($i=$mintotalweeks; $i <= $maxtotalweeks; $i++) {
            $totalweekoptions[$i] = $i;
        }

        $mform->addElement('select', 'config_totalweeks', get_string('blockconfigtotalweeks', 'block_istart_reports'), $totalweekoptions);
        $mform->setDefault('config_totalweeks', $defaulttotalweeks);
//
//        $mform->addElement('html', '<p>'.get_string('managerconfigdesc', 'block_istart_reports').'</p>');
//

    }
}
