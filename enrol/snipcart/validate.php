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

global $DB;

require_once("lib.php");

$plugin = enrol_get_plugin('snipcart');

$userid     = required_param('uid', PARAM_INT);
$enrolid    = required_param('eid',  PARAM_INT);

$user = $DB->get_record('user', array('id'=>$userid));
$enrol = $DB->get_record('enrol', array('id'=>$enrolid));
$course = $DB->get_record('course', array('id'=>$enrol->courseid));
$cost = $enrol->cost;

        
if ( (float) $enrol->cost <= 0 ) {
    $cost = (float) $enrol->get_config('cost');
} else {
    $cost = (float) $enrol->cost;
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
    <p class="course-summary"><img src="<?= $plugin->get_course_image_url($course) ?>" alt="<?= $course->fullname ?>" /><?= $course->summary ?></p>
    <div align="center">

    <p class="payment-required"><?php print_string('paymentrequired', 'enrol_snipcart') ?></p>

    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

    <noscript>
      <div class="alert alert-block alert-error">
          <p><strong><?= get_string('warning', 'moodle') ?></strong> <?= get_string('javascriptrequired', 'group') ?></p>
          <p><?= get_string('nojavascript', 'enrol_snipcart') ?></p>
      </div>
    </noscript>

    <?= $plugin->get_add_to_cart_button($course, $enrol, 'snipcart-add-item'); ?>

    </div>
    </body>
</html>
