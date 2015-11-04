<?php

namespace enrol_snipcart;

/**
 * Factory function for emoticon_manager
 *
 * @return emoticon_manager singleton
 */
function get_snipcartaccounts_manager() {
    static $singleton = null;

    if (is_null($singleton)) {
        $singleton = new snipcartaccounts();
    }

    return $singleton;
}

/**
 * Provides core support for plugins that have to deal with emoticons (like HTML editor or emoticon filter).
 *
 * Whenever this manager mentiones 'emoticon object', the following data
 * structure is expected: stdClass with properties text, imagename, imagecomponent,
 * altidentifier and altcomponent
 *
 * @see admin_setting_emoticons
 *
 * @copyright 2010 David Mudrak
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snipcartaccounts {

    /**
     * Returns the currently enabled emoticons
     *
     * @return array of emoticon objects
     */
    public function get_emoticons() {
        global $CFG;

        if (empty($CFG->emoticons)) {
            return array();
        }

        $emoticons = $this->decode_stored_config($CFG->emoticons);

        if (!is_array($emoticons)) {
            // Something is wrong with the format of stored setting.
            debugging('Invalid format of emoticons setting, please resave the emoticons settings form', DEBUG_NORMAL);
            return array();
        }

        return $emoticons;
    }

    /**
     * Converts emoticon object into renderable pix_emoticon object
     *
     * @param stdClass $emoticon emoticon object
     * @param array $attributes explicit HTML attributes to set
     * @return pix_emoticon
     */
    public function prepare_renderable_emoticon(stdClass $emoticon, array $attributes = array()) {
        $stringmanager = get_string_manager();
        if ($stringmanager->string_exists($emoticon->altidentifier, $emoticon->altcomponent)) {
            $alt = get_string($emoticon->altidentifier, $emoticon->altcomponent);
        } else {
            $alt = s($emoticon->text);
        }
        return new pix_emoticon($emoticon->imagename, $alt, $emoticon->imagecomponent, $attributes);
    }

    /**
     * Encodes the array of emoticon objects into a string storable in config table
     *
     * @see self::decode_stored_config()
     * @param array $emoticons array of emtocion objects
     * @return string
     */
    public function encode_stored_config(array $emoticons) {
        return json_encode($emoticons);
    }

    /**
     * Decodes the string into an array of emoticon objects
     *
     * @see self::encode_stored_config()
     * @param string $encoded
     * @return string|null
     */
    public function decode_stored_config($encoded) {
        $decoded = json_decode($encoded);
        if (!is_array($decoded)) {
            return null;
        }
        return $decoded;
    }

    /**
     * Returns default set of emoticons supported by Moodle
     *
     * @return array of sdtClasses
     */
    public function default_emoticons() {
        return array(
            $this->prepare_emoticon_object(":-)", 's/smiley', 'smiley'),
            $this->prepare_emoticon_object(":)", 's/smiley', 'smiley'),
            $this->prepare_emoticon_object(":-D", 's/biggrin', 'biggrin'),
            $this->prepare_emoticon_object(";-)", 's/wink', 'wink'),
            $this->prepare_emoticon_object(":-/", 's/mixed', 'mixed'),
            $this->prepare_emoticon_object("V-.", 's/thoughtful', 'thoughtful'),
            $this->prepare_emoticon_object(":-P", 's/tongueout', 'tongueout'),
            $this->prepare_emoticon_object(":-p", 's/tongueout', 'tongueout'),
            $this->prepare_emoticon_object("B-)", 's/cool', 'cool'),
            $this->prepare_emoticon_object("^-)", 's/approve', 'approve'),
            $this->prepare_emoticon_object("8-)", 's/wideeyes', 'wideeyes'),
            $this->prepare_emoticon_object(":o)", 's/clown', 'clown'),
            $this->prepare_emoticon_object(":-(", 's/sad', 'sad'),
            $this->prepare_emoticon_object(":(", 's/sad', 'sad'),
            $this->prepare_emoticon_object("8-.", 's/shy', 'shy'),
            $this->prepare_emoticon_object(":-I", 's/blush', 'blush'),
            $this->prepare_emoticon_object(":-X", 's/kiss', 'kiss'),
            $this->prepare_emoticon_object("8-o", 's/surprise', 'surprise'),
            $this->prepare_emoticon_object("P-|", 's/blackeye', 'blackeye'),
            $this->prepare_emoticon_object("8-[", 's/angry', 'angry'),
            $this->prepare_emoticon_object("(grr)", 's/angry', 'angry'),
            $this->prepare_emoticon_object("xx-P", 's/dead', 'dead'),
            $this->prepare_emoticon_object("|-.", 's/sleepy', 'sleepy'),
            $this->prepare_emoticon_object("}-]", 's/evil', 'evil'),
            $this->prepare_emoticon_object("(h)", 's/heart', 'heart'),
            $this->prepare_emoticon_object("(heart)", 's/heart', 'heart'),
            $this->prepare_emoticon_object("(y)", 's/yes', 'yes', 'core'),
            $this->prepare_emoticon_object("(n)", 's/no', 'no', 'core'),
            $this->prepare_emoticon_object("(martin)", 's/martin', 'martin'),
            $this->prepare_emoticon_object("( )", 's/egg', 'egg'),
        );
    }

    /**
     * Helper method preparing the stdClass with the emoticon properties
     *
     * @param string|array $text or array of strings
     * @param string $imagename to be used by {@link pix_emoticon}
     * @param string $altidentifier alternative string identifier, null for no alt
     * @param string $altcomponent where the alternative string is defined
     * @param string $imagecomponent to be used by {@link pix_emoticon}
     * @return stdClass
     */
    protected function prepare_emoticon_object($text, $imagename, $altidentifier = null,
                                               $altcomponent = 'core_pix', $imagecomponent = 'core') {
        return (object)array(
            'text'           => $text,
            'imagename'      => $imagename,
            'imagecomponent' => $imagecomponent,
            'altidentifier'  => $altidentifier,
            'altcomponent'   => $altcomponent,
        );
    }
}
