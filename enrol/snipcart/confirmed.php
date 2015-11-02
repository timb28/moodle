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

require_login();

$order = required_param('order', PARAM_ALPHANUMEXT);

$plugin = enrol_get_plugin('snipcart');

$validatedorder = $plugin->snipcart_get_order($order);

// todo: record event in the Moodle event log

$context = context_system::instance();

$PAGE->set_url('/enrol/snipcart/confirmed.php', array('order'=>$order));
$PAGE->set_cacheable(false);
$PAGE->set_context($context);

$PAGE->set_heading(get_string('ordercomplete', 'enrol_snipcart'));
$PAGE->set_title(get_string('ordercomplete', 'enrol_snipcart'));
echo $OUTPUT->header();


// todo: display links to each course that has been bought.

?>
<div>Order: <?php echo print_r($validatedorder, true) ?></div>

<?php

echo $OUTPUT->footer();

?>

