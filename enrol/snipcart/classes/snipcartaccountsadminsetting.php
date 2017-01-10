<?php

/**
 * Snipcart enrolments plugin accounts admin setting.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('snipcartaccounts.php');
require_once($CFG->libdir.'/adminlib.php');

/**
 * Administration interface for snipcartaccounts_manager settings.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_snipcartaccounts extends admin_setting {

    /**
     * Calls parent::__construct with specific args
     */
    public function __construct() {
        global $CFG;

        $manager = \enrol_snipcart\get_snipcartaccounts_manager();
        $defaults = $this->prepare_form_data($manager->default_snipcartaccounts());
        parent::__construct('enrol_snipcart/snipcartaccounts', get_string('snipcartaccounts', 'enrol_snipcart'), get_string('snipcartaccounts_desc', 'enrol_snipcart'), $defaults);
    }

    /**
     * Return the current setting(s)
     *
     * @return array Current settings array
     */
    public function get_setting() {
        global $CFG;

        $manager = \enrol_snipcart\get_snipcartaccounts_manager();

        $config = $this->config_read($this->name);
        if (is_null($config)) {
            return null;
        }

        $config = $manager->decode_stored_config($config);
        if (is_null($config)) {
            return null;
        }

        return $this->prepare_form_data($config);
    }

    /**
     * Save selected settings
     *
     * @param array $data Array of settings to save
     * @return bool
     */
    public function write_setting($data) {

        $manager = \enrol_snipcart\get_snipcartaccounts_manager();
        $snipcartaccounts = $this->process_form_data($data);

        if ($snipcartaccounts === false) {
            return false;
        }

        if ($this->config_write($this->name, $manager->encode_stored_config($snipcartaccounts))) {
            return ''; // success
        } else {
            return get_string('errorsetting', 'admin') . $this->visiblename . html_writer::empty_tag('br');
        }
    }

    /**
     * Return XHTML field(s) for options
     *
     * @param array $data Array of options to set in HTML
     * @return string XHTML string for the fields and wrapping div(s)
     */
    public function output_html($data, $query='') {
        global $OUTPUT;
        
        

        $out  = html_writer::start_tag('table', array('id' => 'snipcartaccountssetting', 'class' => 'admintable generaltable'));
        $out .= html_writer::start_tag('thead');
        $out .= html_writer::start_tag('tr');
        $out .= html_writer::tag('th', get_string('accountname', 'enrol_snipcart'));
        $out .= html_writer::tag('th', get_string('country', 'core'));
        $out .= html_writer::tag('th', get_string('currency', 'enrol_snipcart'));
        $out .= html_writer::tag('th', get_string('currencyformat', 'enrol_snipcart'));
        $out .= html_writer::tag('th', get_string('publicapikey', 'enrol_snipcart'));
        $out .= html_writer::tag('th', get_string('privateapikey', 'enrol_snipcart'));
        $out .= html_writer::end_tag('tr');
        $out .= html_writer::end_tag('thead');
        $out .= html_writer::start_tag('tbody');
        $i = 0;
        foreach($data as $field => $value) {
            switch ($i) {
                case 5:
                    $inputtype = 'password';
                    break;
                default:
                    $inputtype = 'text';
                    break;
            }
            
            if ($i == 0) {
                $out .= html_writer::start_tag('tr');
            }

            $out .= html_writer::tag('td',
                html_writer::empty_tag('input',
                    array(
                        'type'  => $inputtype,
                        'class' => 'form-text',
                        'name'  => $this->get_full_name().'['.$field.']',
                        'value' => $value,
                    )
                ), array('class' => 'c'.$i)
            );

            if ($i == 5) {
                $out .= html_writer::end_tag('tr');
                $i = 0;
            } else {
                $i++;
            }

        }
        $out .= html_writer::end_tag('tbody');
        $out .= html_writer::end_tag('table');
        
        $settingstyles = new moodle_url('/enrol/snipcart/settings.css');
        $out .= html_writer::tag('link', '', array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>$settingstyles));
        
        $output  = html_writer::tag('div', $out, array('class' => 'form-group'));

        return format_admin_setting($this, $this->visiblename, $output, $this->description, false, '', NULL, $query);
    }

    /**
     * Converts the array of snipcartaccount objects provided by {@see snipcartaccounts_manager} into admin settings form data
     *
     * @see self::process_form_data()
     * @param array $snipcartaccounts array of snipcartaccount objects as returned by {@see snipcartaccounts_manager}
     * @return array of form fields and their values
     */
    protected function prepare_form_data(array $snipcartaccounts) {

        $form = array();
        $i = 0;
        foreach ($snipcartaccounts as $snipcartaccount) {
            $form['name'.$i]            = $snipcartaccount->name;
            $form['countrycode'.$i]     = $snipcartaccount->countrycode;
            $form['currencycode'.$i]    = $snipcartaccount->currencycode;
            $form['currencyformat'.$i]  = $snipcartaccount->currencyformat;
            $form['publicapikey'.$i]    = $snipcartaccount->publicapikey;
            $form['privateapikey'.$i]   = $snipcartaccount->privateapikey;
            $i++;
        }
        // add one more blank field set for new object
        $form['name'.$i]            = '';
        $form['countrycode'.$i]     = '';
        $form['currencycode'.$i]    = '';
        $form['currencyformat'.$i]  = '';
        $form['publicapikey'.$i]    = '';
        $form['privateapikey'.$i]   = '';

        return $form;
    }

    /**
     * Converts the data from admin settings form into an array of snipcartaccount objects
     *
     * @see self::prepare_form_data()
     * @param array $data array of admin form fields and values
     * @return false|array of snipcartaccount objects
     */
    protected function process_form_data(array $form) {

        $count = count($form); // number of form field values

        if ($count % 6) {
            // we must get six fields per snipcartaccount object
            return false;
        }

        $snipcartaccounts = array();
        for ($i = 0; $i < $count / 6; $i++) {
            $snipcartaccount            = new stdClass();
            $snipcartaccount->name              = clean_param(trim($form['name'.$i]), PARAM_NOTAGS);
            $snipcartaccount->countrycode       = clean_param(trim($form['countrycode'.$i]), PARAM_ALPHA);
            $snipcartaccount->currencycode      = clean_param(trim($form['currencycode'.$i]), PARAM_ALPHA);
            $snipcartaccount->currencyformat    = clean_param(trim($form['currencyformat'.$i]), PARAM_NOTAGS);
            $snipcartaccount->publicapikey      = clean_param(trim($form['publicapikey'.$i]), PARAM_ALPHANUM);
            $snipcartaccount->privateapikey     = clean_param(trim($form['privateapikey'.$i]), PARAM_ALPHANUMEXT);

            if (    $snipcartaccount->name              !== '' and
                    $snipcartaccount->countrycode       !== '' and
                    $snipcartaccount->currencycode      !== '' and
                    $snipcartaccount->currencyformat    !== '' and
                    $snipcartaccount->publicapikey      !== '' and
                    $snipcartaccount->privateapikey     !== ''
                ) {
                $snipcartaccounts[] = $snipcartaccount;
            }
        }
        return $snipcartaccounts;
    }
}
