<?php
/* 
 * Empty class as all the functionality is in index.php
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_harcourtsone extends auth_plugin_base {
    /**
     * Constructor.
     */
    public function __construct() {
        $this->authtype = 'harcourtsone';
    }
    
    /**
     * Do not allow any direct login.
     *
     */
    function user_login($username, $password) {
        return false;
    }
}
