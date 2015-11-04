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
 * Administration interface for emoticon_manager settings.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_snipcartaccounts extends admin_setting {

    /**
     * Calls parent::__construct with specific args
     */
    public function __construct() {
        global $CFG;

        $manager = get_snipcartaccounts_manager();
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

        $manager = get_emoticon_manager();

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

        $manager = get_emoticon_manager();
        $emoticons = $this->process_form_data($data);

        if ($emoticons === false) {
            return false;
        }

        if ($this->config_write($this->name, $manager->encode_stored_config($emoticons))) {
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

        $out  = html_writer::start_tag('table', array('id' => 'emoticonsetting', 'class' => 'admintable generaltable'));
        $out .= html_writer::start_tag('thead');
        $out .= html_writer::start_tag('tr');
        $out .= html_writer::tag('th', get_string('emoticontext', 'admin'));
        $out .= html_writer::tag('th', get_string('emoticonimagename', 'admin'));
        $out .= html_writer::tag('th', get_string('emoticoncomponent', 'admin'));
        $out .= html_writer::tag('th', get_string('emoticonalt', 'admin'), array('colspan' => 2));
        $out .= html_writer::tag('th', '');
        $out .= html_writer::end_tag('tr');
        $out .= html_writer::end_tag('thead');
        $out .= html_writer::start_tag('tbody');
        $i = 0;
        foreach($data as $field => $value) {
            switch ($i) {
            case 0:
                $out .= html_writer::start_tag('tr');
                $current_text = $value;
                $current_filename = '';
                $current_imagecomponent = '';
                $current_altidentifier = '';
                $current_altcomponent = '';
            case 1:
                $current_filename = $value;
            case 2:
                $current_imagecomponent = $value;
            case 3:
                $current_altidentifier = $value;
            case 4:
                $current_altcomponent = $value;
            }

            $out .= html_writer::tag('td',
                html_writer::empty_tag('input',
                    array(
                        'type'  => 'text',
                        'class' => 'form-text',
                        'name'  => $this->get_full_name().'['.$field.']',
                        'value' => $value,
                    )
                ), array('class' => 'c'.$i)
            );

            if ($i == 4) {
                if (get_string_manager()->string_exists($current_altidentifier, $current_altcomponent)) {
                    $alt = get_string($current_altidentifier, $current_altcomponent);
                } else {
                    $alt = $current_text;
                }
                if ($current_filename) {
                    $out .= html_writer::tag('td', $OUTPUT->render(new pix_emoticon($current_filename, $alt, $current_imagecomponent)));
                } else {
                    $out .= html_writer::tag('td', '');
                }
                $out .= html_writer::end_tag('tr');
                $i = 0;
            } else {
                $i++;
            }

        }
        $out .= html_writer::end_tag('tbody');
        $out .= html_writer::end_tag('table');
        $out  = html_writer::tag('div', $out, array('class' => 'form-group'));
        $out .= html_writer::tag('div', html_writer::link(new moodle_url('/admin/resetemoticons.php'), get_string('emoticonsreset', 'admin')));

        return format_admin_setting($this, $this->visiblename, $out, $this->description, false, '', NULL, $query);
    }

    /**
     * Converts the array of emoticon objects provided by {@see emoticon_manager} into admin settings form data
     *
     * @see self::process_form_data()
     * @param array $emoticons array of emoticon objects as returned by {@see emoticon_manager}
     * @return array of form fields and their values
     */
    protected function prepare_form_data(array $emoticons) {

        $form = array();
        $i = 0;
        foreach ($emoticons as $emoticon) {
            $form['text'.$i]            = $emoticon->text;
            $form['imagename'.$i]       = $emoticon->imagename;
            $form['imagecomponent'.$i]  = $emoticon->imagecomponent;
            $form['altidentifier'.$i]   = $emoticon->altidentifier;
            $form['altcomponent'.$i]    = $emoticon->altcomponent;
            $i++;
        }
        // add one more blank field set for new object
        $form['text'.$i]            = '';
        $form['imagename'.$i]       = '';
        $form['imagecomponent'.$i]  = '';
        $form['altidentifier'.$i]   = '';
        $form['altcomponent'.$i]    = '';

        return $form;
    }

    /**
     * Converts the data from admin settings form into an array of emoticon objects
     *
     * @see self::prepare_form_data()
     * @param array $data array of admin form fields and values
     * @return false|array of emoticon objects
     */
    protected function process_form_data(array $form) {

        $count = count($form); // number of form field values

        if ($count % 5) {
            // we must get five fields per emoticon object
            return false;
        }

        $emoticons = array();
        for ($i = 0; $i < $count / 5; $i++) {
            $emoticon                   = new stdClass();
            $emoticon->text             = clean_param(trim($form['text'.$i]), PARAM_NOTAGS);
            $emoticon->imagename        = clean_param(trim($form['imagename'.$i]), PARAM_PATH);
            $emoticon->imagecomponent   = clean_param(trim($form['imagecomponent'.$i]), PARAM_COMPONENT);
            $emoticon->altidentifier    = clean_param(trim($form['altidentifier'.$i]), PARAM_STRINGID);
            $emoticon->altcomponent     = clean_param(trim($form['altcomponent'.$i]), PARAM_COMPONENT);

            if (strpos($emoticon->text, ':/') !== false or strpos($emoticon->text, '//') !== false) {
                // prevent from breaking http://url.addresses by accident
                $emoticon->text = '';
            }

            if (strlen($emoticon->text) < 2) {
                // do not allow single character emoticons
                $emoticon->text = '';
            }

            if (preg_match('/^[a-zA-Z]+[a-zA-Z0-9]*$/', $emoticon->text)) {
                // emoticon text must contain some non-alphanumeric character to prevent
                // breaking HTML tags
                $emoticon->text = '';
            }

            if ($emoticon->text !== '' and $emoticon->imagename !== '' and $emoticon->imagecomponent !== '') {
                $emoticons[] = $emoticon;
            }
        }
        return $emoticons;
    }
}
