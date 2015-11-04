<?php

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
     * Returns the currently enabled snipcartaccounts
     *
     * @return array of snipcartaccounts objects
     */
    public function get_snipcartaccounts() {
        global $CFG;

        if (empty($CFG->emoticons)) {
            return array();
        }

        $snipcartaccounts = $this->decode_stored_config($CFG->enrol_snipcart_snipcartaccounts);

        if (!is_array($snipcartaccounts)) {
            // Something is wrong with the format of stored setting.
            debugging('Invalid format of Snipcart Accounts setting, please resave the Snipcart Accounts settings form', DEBUG_NORMAL);
            return array();
        }

        return $snipcartaccounts;
    }

    /**
     * Encodes the array of snipcartaccounts objects into a string storable in config table
     *
     * @see self::decode_stored_config()
     * @param string[] array of snipcartaccounts objects
     * @return string
     */
    public function encode_stored_config(array $snipcartaccounts) {
        return json_encode($snipcartaccounts);
    }

    /**
     * Decodes the string into an array of snipcartaccounts objects
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
     * Returns default set of snipcart accounts
     *
     * @return array of sdtClasses
     */
    public function default_snipcartaccounts() {
        return array(
            $this->prepare_snipcartaccount_object('Australia', 'AU', 'AUD', '$%c', 'Public API Key', 'Private API Key')
        );
    }

    /**
     * Helper method preparing the stdClass with the snipcart account properties
     *
     * @param string $countryname of the country
     * @param string $countrycode international standard used in the user country field
     * @param string $currencycode international standard used in the enrol table
     * @param string $currencysymbol for localising prices
     * @param string $publicapikey for the Snipcart account
     * @param string $privateapikey for the Snipcar account
     * @return stdClass
     */
    protected function prepare_snipcartaccount_object($countryname, $countrycode, $currencycode,
                                               $currencysymbol, $publicapikey, $privateapikey) {
        return (object)array(
            'countryname'       => $countryname,
            'countrycode'       => $countrycode,
            'currencycode'      => $currencycode,
            'currencysymbol'    => $currencysymbol,
            'publicapikey'      => $publicapikey,
            'privateapikey'     => $privateapikey,
        );
    }
}
