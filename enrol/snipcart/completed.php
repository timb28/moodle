<?php

/**
 * Adds new instance of enrol_snipcart to specified course
 * or edits current instance.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $DB;

require_login();

if (isguestuser()) {
    redirect(new moodle_url('/', array('redirect' => 0)));
}

$token = required_param('order', PARAM_ALPHANUMEXT);
$instanceid = required_param('i', PARAM_INT);

$plugin = enrol_get_plugin('snipcart');

$instance = $DB->get_record('enrol', array('id'=>$instanceid));

$validatedorder = $plugin->snipcart_get_order_from_token_and_currency($token, $instance->currency);

$userid = $USER->id;  // Owner of the page
$context = context_system::instance();

$PAGE->set_url('/enrol/snipcart/confirmed.php', array('order'=>$token, 'i'=>$instanceid));
$PAGE->set_pagelayout('mydashboard');
$PAGE->blocks->add_region('content');
$PAGE->set_cacheable(false);
$PAGE->set_context($context);

$ordermessage = '';
switch ($validatedorder['status']) {
    case 'Processed':
        $ordermessage = get_string('orderthankyou', 'enrol_snipcart');
        break;
    case 'Cancelled':
        $ordermessage = get_string('ordercancelled', 'enrol_snipcart');
        break;
    case 'Disputed':
        $ordermessage = get_string('orderdisputed', 'enrol_snipcart');
        break;
    case 'Pending':
        $ordermessage = get_string('orderpending', 'enrol_snipcart');
        break;
}

$PAGE->set_title(get_string('ordercomplete', 'enrol_snipcart'));
$PAGE->navbar->add(get_string('ordercomplete', 'enrol_snipcart'));
$PAGE->set_heading(get_string('ordercomplete', 'enrol_snipcart'));
echo $OUTPUT->header();

?>

<p class="lead"><?= $ordermessage ?></p>

<div class="row-fluid">
    <div class="span8"><h4><?= get_string('orderitem', 'enrol_snipcart') ?></h4></div>
    <div class="span4"><h4><?= get_string('orderprice', 'enrol_snipcart') ?></h4></div>
</div>
<?php

$totalpaid = 0;
$ordercurrency = '';

foreach($validatedorder['items'] as $item) { 
    $coursename = $item['name'];
    $courseprice = $item['totalPrice'];
    
    $orderinstance = $plugin->snipcart_get_instance_from_itemid($item['id']);
    $course = $plugin->snipcart_get_course_from_itemid($item['id']);
    $courselink = new moodle_url('/course/view.php', array('id' => $course->id));
    $ordercurrency = $orderinstance->currency; // All order items use the same currency

    $localisedcost = $plugin->get_localised_currency($ordercurrency, format_float($courseprice, 2, true));
    $totalpaid+= $courseprice;
?>

<div class="row-fluid">
  <div class="span8"><?= "<a href='$courselink'>$coursename</a>" ?></div>
  <div class="span4"><?= $localisedcost ?></div>  
</div>

<?php

}

$localisedtotalpaid = $plugin->get_localised_currency($ordercurrency, format_float($totalpaid, 2, true));

?>

<div class="row-fluid">
    <div class="span8"><h5><?= get_string('ordertotal', 'enrol_snipcart') ?></h5></div>
    <div class="span4"><h5><?= $localisedtotalpaid ?></h5></div>  
</div>

<?php

echo $OUTPUT->footer();

?>


