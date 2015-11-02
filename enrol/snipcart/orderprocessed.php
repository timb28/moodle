<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$PAGE->set_title(get_string('ordercomplete', 'enrol_snipcart'));
$PAGE->navbar->add(get_string('ordercomplete', 'enrol_snipcart'));
$PAGE->set_heading(get_string('ordercomplete', 'enrol_snipcart'));
echo $OUTPUT->header();

?>

<p class="lead"><?= get_string('orderthankyou', 'enrol_snipcart') ?></p>

<div class="row-fluid">
    <div class="span8"><h4>Course</h4></div>
    <div class="span4"><h4>Price</h4></div>
</div>
<?php

$totalpaid = 0;

foreach($validatedorder['items'] as $item) { 
    $coursename = $item['name'];
    $courseprice = $item['totalPrice'];
    $localisedcost = format_float($courseprice, 2, true);
    $totalpaid+= $courseprice;
    
    $instance = $plugin->snipcart_get_instance_from_itemid($item['id']);
    $course = $plugin->snipcart_get_course_from_itemid($item['id']);
    $courselink = new moodle_url('/course/index.php', array('id' => $course->id));
?>

<div class="row-fluid">
  <div class="span8"><?= "<a href='$courselink'>$coursename</a>" ?></div>
  <div class="span4"><?= $localisedcost ?></div>  
</div>

<?php

}

$localisedtotalpaid = format_float($totalpaid, 2, true);

?>

<div class="row-fluid">
    <div class="span8"><h5>TOTAL PAID</h5></div>
    <div class="span4"><h5><?= $localisedtotalpaid ?></h5></div>  
</div>

<?php

echo $OUTPUT->footer();

?>

