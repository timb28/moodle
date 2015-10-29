<?php

/**
 * Called by Snipcart to validate product prices
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Disable moodle specific debug messages and any errors in output,
// comment out when debugging or better look into error log!
define('NO_DEBUG_DISPLAY', true);

require('../../config.php');

require_once("lib.php");

$plugin = enrol_get_plugin('snipcart');

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

        <script type="text/javascript" src="https://cdn.snipcart.com/scripts/snipcart.js" id="snipcart"
          data-api-key="<?php echo( $plugin->get_config('publicapikey') ); ?>"></script>

        <link type="text/css" href="https://cdn.snipcart.com/themes/base/snipcart.min.css" rel="stylesheet" />
    </body>
</html>
