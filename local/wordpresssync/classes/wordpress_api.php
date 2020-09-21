<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Creates and executes all WordPress API calls.
 *
 * @package     local_wordpresssync
 * @copyright   2020 Harcourts International Pty Ltd <academy@harcourts.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_wordpresssync;

defined('MOODLE_INTERNAL') || die();

class wordpress_api
{
    private $ch = null;
    private $success = false;

    /** Build the cURL session
     *
     * @param bool $ispost
     * @param string $endpoint
     * @param array $query
     * @param array $postfields
     * @throws \dml_exception
     */
    function __construct(bool $ispost = false, string $endpoint, array $query, array $postfields = null) {
        global $CFG;

        if( !function_exists("curl_init") &&
            !function_exists("curl_setopt") &&
            !function_exists("curl_exec") &&
            !function_exists("curl_close") ) die ("cURL not installed.");

        $wpurl = get_config('local_wordpresssync', 'wpurl');
        if (empty($wpurl))
            return false;

        $wpurl.= $endpoint;
        $wpusername = get_config('local_wordpresssync', 'wpusername');
        $wppassword = get_config('local_wordpresssync', 'wppassword');


        if (empty($wpusername) || empty($wppassword)) {
            debugging('local_wordpresssync: WordPress settings not yet configured.');
            return false;
        }

        if (!preg_match('|^https://|i', $wpurl)) {
            debugging('local_wordpresssync: WordPress URL must use HTTPS.');
            return false;
        }

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $wpurl . "?"
            . http_build_query($query, null, '&'));
        if ($ispost) {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 4);
        if ($CFG->debugdeveloper) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        curl_setopt($this->ch, CURLOPT_USERPWD, $wpusername . ":" . $wppassword);
    }

    /**
     * Execute the request.
     */
    public function execute() {
        $response = curl_exec($this->ch);

        // Get the HTTP status from the response header.
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        if( $httpcode>=200 && $httpcode<300 ) $this->success = true;

        // Close the session to free resources
        curl_close($this->ch);

        return $response;

    }

    /**
     * Check if the the request was a success based on the HTTP status code being 2xx.
     *
     * @return true|false
     */
    public function get_success() {

        return $this->success;
    }

}