<?php
/**
 * Authentication Plugin: Harcourts One Authentication
 *
 * Checks against an external Harcourts One server.
 *
 * @package    auth
 * @subpackage harcourtsone
 * @author     Tim Butler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

require_once("../../config.php");

// Check if the return url was passed as a parameter
$returnUrl = optional_param('return', '/', PARAM_TEXT);

$PAGE->set_url('/auth/harcourtsone/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('navbartitle', 'auth_harcourtsone'));
$PAGE->set_title(get_string('title', 'auth_harcourtsone'));
$PAGE->set_heading(get_string('heading', 'auth_harcourtsone'));
echo $OUTPUT->header();


?>
<div class="box">
    <div class="h1-link-wrapper"><a id="australia" href="http://one.harcourts.com.au/e-cademy/apps/moodleauth.aspx?return=<?php echo $returnUrl; ?>"><span class="h1-link-text-overlay">Australia</span></a></div>
    <div class="h1-link-wrapper"><a id="new-zealand" href="http://one.harcourts.co.nz/e-cademy/apps/moodleauth.aspx?return=<?php echo $returnUrl; ?>"><span class="h1-link-text-overlay">New Zealand</span></a></div>
    <div class="h1-link-wrapper"><a id="south-africa" href="http://one.harcourts.co.za/e-cademy/apps/moodleauth.aspx?return=<?php echo $returnUrl; ?>"><span class="h1-link-text-overlay">South Africa</span></a></div>
    <div class="h1-link-wrapper"><a id="usa" href="http://one.harcourtsusa.com/e-cademy/apps/moodleauth.aspx?return=<?php echo $returnUrl; ?>"><span class="h1-link-text-overlay">USA</span></a></div>
    <div class="clearfix"></div>
</div>
<?php
echo $OUTPUT->footer();
?>
