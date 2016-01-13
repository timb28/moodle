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

use enrol_snipcart\snipcartorder;

global $DB;

require_login();

if (isguestuser()) {
    redirect(new moodle_url('/', array('redirect' => 0)));
}

$ordertoken = required_param('order', PARAM_ALPHANUMEXT);
$enrolid = required_param('eid', PARAM_INT);

if (! $enrol = $DB->get_record('enrol', array('id'=>$enrolid))) {
    header('HTTP/1.1 400 BAD REQUEST');
    throw new moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$ordertoken, 'currency'=>'unknown'));
}

$snipcartorder = new snipcartorder($ordertoken, $enrol->currency);

if ($snipcartorder->user->id != $USER->id) {
    header('HTTP/1.1 400 BAD REQUEST');
    throw new moodle_exception('snipcartinvalidorderror', 'enrol_snipcart', null, array('token'=>$ordertoken, 'currency'=>$enrol->currency));
}

$userid = $USER->id;  // Owner of the page
$context = context_system::instance();

$PAGE->set_url('/enrol/snipcart/confirmed.php', array('order'=>$ordertoken, 'eid'=>$enrolid));
$PAGE->set_pagelayout('mydashboard');
$PAGE->blocks->add_region('content');
$PAGE->set_cacheable(false);
$PAGE->set_context($context);

$ordermessage = '';
switch ($snipcartorder->status) {
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

$ordercurrency = '';

foreach($snipcartorder->items as $item) { 
    $coursename = $item['name'];
    $courseprice = $item['totalPrice'];
    $courselink = $item['courselink'];
    
    $localisedcost = $snipcartorder->plugin->get_localised_currency($snipcartorder->currency, format_float($courseprice, 2, true));
?>

<div class="row-fluid">
  <div class="span8"><?= "<a href='$courselink'>$coursename</a>" ?></div>
  <div class="span4"><?= $localisedcost ?></div>  
</div>

<?php

}

$localisedtotalpaid = $snipcartorder->plugin->get_localised_currency($snipcartorder->currency, format_float($snipcartorder->totalprice, 2, true));

?>

<hr style="width: 90%;" />

<?php

foreach ($snipcartorder->taxes as $tax) {
    
    $localisedtaxcost = $snipcartorder->plugin->get_localised_currency($snipcartorder->currency, format_float($tax['amount'], 2, true));
?>
<div class="row-fluid">
  <div class="span8"><?= $tax['taxName'] ?></div>
  <div class="span4"><?= $localisedtaxcost ?></div>  
</div>

<?php

}
?>


<div class="row-fluid">
    <div class="span8"><h5><?= get_string('ordertotal', 'enrol_snipcart') ?></h5></div>
    <div class="span4"><h5><?= $localisedtotalpaid ?></h5></div>  
</div>

<?php

echo $OUTPUT->footer();

?>


