<?php

namespace enrol_snipcart;

/**
 * Factory function for snipcartaccounts_manager
 *
 * @return snipcartaccounts_manager singleton
 */
function get_snipcartaccounts_manager() {
    static $singleton = null;

    if (is_null($singleton)) {
        $singleton = new snipcartaccounts();
    }

    return $singleton;
}

/**
 * Provides support for enrol_snipcart
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
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
        
        $snipcart = enrol_get_plugin('snipcart');
        
        if (empty($snipcart->get_config('snipcartaccounts'))) {
            return array();
        }
        
        $snipcartaccounts = $this->decode_stored_config($snipcart->get_config('snipcartaccounts'));

        if (!is_array($snipcartaccounts)) {
            // Something is wrong with the format of stored setting.
            debugging('Invalid format of Snipcart Accounts setting, please resave the Snipcart Accounts settings form', DEBUG_NORMAL);
            return array();
        }

        return $snipcartaccounts;
    }
    
    /**
     * Returns specific information about a single snipcartaccount
     * using it's currency as the reference
     *
     * @param string $currencycode of the snipcart account of interest
     * @param string $field name of the field to return
     * 
     * @return string of content of snipcart account data
     */
    public function get_snipcartaccount_info($currencycode, $field) {
        
        $snipcartaccounts = $this->get_snipcartaccounts();
        
        foreach ($snipcartaccounts as $account) {
            if ($account->currencycode == $currencycode) {
                return $account->$field;
            }
        }

        return null;
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
            $this->prepare_snipcartaccount_object('Australia', 'AU', 'AUD', '$%c', 'PublicAPIKey', 'PrivateAPIKey')
        );
    }

    /**
     * Helper method preparing the stdClass with the snipcart account properties
     *
     * @param string $name of the Snipcart account
     * @param string $countrycode international standard used in the user country field
     * @param string $currencycode international standard used in the enrol table
     * @param string $currencyformat for localising prices
     * @param string $publicapikey for the Snipcart account
     * @param string $privateapikey for the Snipcar account
     * @return stdClass
     */
    protected function prepare_snipcartaccount_object($name, $countrycode, $currencycode,
                                               $currencyformat, $publicapikey, $privateapikey) {
        return (object)array(
            'name'              => $name,
            'countrycode'       => $countrycode,
            'currencycode'      => $currencycode,
            'currencyformat'    => $currencyformat,
            'publicapikey'      => $publicapikey,
            'privateapikey'     => $privateapikey,
        );
    }
}
