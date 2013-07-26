<?php
/**
 * Harcourts library functions.
 *
 * @package   harcourts
 * @copyright 2011 onwards Harcourts Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Gets the Harcourts One Token
 *
 * @return string
 */
function academy_get_h1token() {
    global $CFG;
    global $PAGE;

    $h1Token = null;

    // Check if the token was passed as a parameter
    $h1Token = optional_param('h1token', false, PARAM_ALPHANUM);

    // Check if the h1 server was passed as a parameter
    $h1Server = optional_param('h1server', false, PARAM_TEXT);

    if ($h1Token == null) {
        // Check if the token is stored in a cookie
        $h1Token = @$_COOKIE['h1token'];
    } else {
        // Store the url token in a session cookie
        setcookie('h1token', $h1Token, 0, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $CFG->cookiesecure, $CFG->cookiehttponly);

        // If the H1 server was passed as a parameter, store it then determine and store the user's country
        if ($h1Server != null) {
            // Store the h1 server in a session cookie
            setcookie('h1server', $h1Server, 0, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $CFG->cookiesecure, $CFG->cookiehttponly);

            $userCountry = get_countrycode_from_h1hostname($h1Server);

            if ($userCountry == null) { die ("Invalid Harcourts One Hostname: " . $h1Server); }

            // Store the user's country in a session cookie
            setcookie('usercountry', $userCountry, 0, $CFG->sessioncookiepath, $CFG->sessioncookiedomain, $CFG->cookiesecure, $CFG->cookiehttponly);
        }

        // Remove the h1 token and server from the url and reload the page
        $redirectParams = $PAGE->url->remove_params("h1token", "h1server");
        $redirectUrl = new moodle_url(strip_querystring($PAGE->url));
        $redirectUrl->params($redirectParams);
        redirect($redirectUrl);
    }

    return $h1Token;
}

/**
 * Gets the user's country
 *
 * @return string of country code
 */
function academy_get_usercountry() {
    $country = optional_param('country', null, PARAM_TEXT);

    if ($country == null) {
        $country = @$_COOKIE['usercountry'];
    }

    return $country;
}

/**
 * Checks if Harcourts One Token is valid
 *
 * @param string $harcourtsoneurl
 * @param string $h1Token
 * @return true if token is valid
 * @return false if token is invalid
 */
function academy_check_h1token($h1Token, $userCountry) {
    global $SESSION;

    // Check for valid H1 session
    if (isset($SESSION->validh1session) && $SESSION->validh1session) {
        return true;
    }

    if( !function_exists("curl_init") &&
          !function_exists("curl_setopt") &&
          !function_exists("curl_exec") &&
          !function_exists("curl_close") ) die ("cURL not installed.");

    $validToken = false;

    // Construct the URL with an encoded H1 Token
    $harcourtsoneserver = get_h1hostname_from_countrycode($userCountry);

    if ($userCountry == null) { die ("Invalid country code: " . $userCountry); }

    $url = "http://" . $harcourtsoneserver . "/e-cademy/apps/validateh1token.aspx";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 1);                // Include the header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // Don't echo the response
    curl_setopt($ch, CURLOPT_NOBODY, true);             // We don't care about the response body
    curl_setopt($ch, CURLOPT_COOKIE, 'ASP_H1A=' . $h1Token);

    // Execute the request
    $response = curl_exec($ch);

    // Get the HTTP status from the response header.
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if( $httpcode>=200 && $httpcode<300 ) {
        $validToken = true;
        $SESSION->validh1session = true;
    }

    // close cURL resource, and free up system resources
    curl_close($ch);

    return $validToken;
}

/**
 * Authorise H1 user
 *
 * @param string $harcourtsoneurl
 * @param string $h1Token
 * @return true if token is valid
 * @return false if token is invalid
 */
function academy_h1_authorisation ($thisPageURL) {
    global $PAGE;

    $h1MoodleAuthUrl = new moodle_url("/auth/harcourtsone/", array("return" => $thisPageURL));

    // Get the H1 Token
    $h1TokenValue = academy_get_h1token();

    // Get the user's country
    $userCountry = academy_get_usercountry();

    // Redirect directly to H1 login for visitor's country if the country is given
    if ($h1TokenValue == null && $userCountry != null) {
        $h1server = get_h1hostname_from_countrycode($userCountry);
        if ($h1server != null) {
            $h1MoodleAuthUrl = "http://" . $h1server . "/e-cademy/apps/moodleauth.aspx?return=" . $thisPageURL;

            redirect($h1MoodleAuthUrl); // Redirect to H1 script if no tokens or country
        }
    }

    // Redirect to H1 Login if H1 Token or user country is missing
    if ($h1TokenValue == null || $userCountry == null) {
        redirect($h1MoodleAuthUrl); // Redirect to H1 script if no tokens or country
    }

    // Redirect to H1 Login if H1 Token is invalid
    if (!academy_check_h1token($h1TokenValue, $userCountry)) {
        redirect($h1MoodleAuthUrl);
    }
}

/**
 * Gets country code from Harcourts One hostname
 *
 * @param string $harcourtsonehostname
 * @return string of two letter country code
 */
function get_countrycode_from_h1hostname ($harcourtsonehostname) {
    $countrycode = null;

    switch ($harcourtsonehostname) {
        case "one.harcourts.com.au":
            $countrycode = "au";
            break;

        case "one.harcourts.co.nz":
            $countrycode = "nz";
            break;

        case "one.harcourtsusa.com":
            $countrycode = "us";
            break;

        case "one.harcourts.co.za":
            $countrycode = "za";
            break;
    }

    return $countrycode;
}

/**
 * Gets Harcourts One hostname from country code
 *
 * @param string $countrycode
 * @return string Harcourts One hostname
 */
function get_h1hostname_from_countrycode ($countrycode) {
    $harcourtsoneserver = null;
    switch ($countrycode) {
        case "au":
            $harcourtsoneserver = "one.harcourts.com.au";
            break;

        case "nz":
            $harcourtsoneserver = "one.harcourts.co.nz";
            break;

        case "us":
            $harcourtsoneserver = "one.harcourtsusa.com";
            break;

        case "za":
            $harcourtsoneserver = "one.harcourts.co.za";
            break;
    }

    return $harcourtsoneserver;
}

/**
 * Gets Harcourts Answers link from country code
 *
 * @param string $countrycode
 * @return string Harcourts Answers link
 */
function get_harcourtsanswerslink () {
    $harcourtsanswerslink = null;
    
    $countrycode = @$_COOKIE['usercountry'];
    
    if (get_h1hostname_from_countrycode($countrycode) !== null) {
        $harcourtsanswerslink = "http://" . get_h1hostname_from_countrycode($countrycode) . '/Apps/Answers/Home.mvc';
    }
    
    return $harcourtsanswerslink;
}

?>
