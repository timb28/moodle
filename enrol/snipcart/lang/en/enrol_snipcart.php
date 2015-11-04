<?php

/**
 * Strings for component 'enrol_snipcart', language 'en'.
 *
 * @package   enrol_snipcart
 * @author    Tim Butler
 * @copyright (c) 2015 Harcourts International Limited {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accountname'] = 'Name';
$string['addtocart'] = '<i class="icon-shopping-cart icon-white"></i> Add to cart';
$string['assignrole'] = 'Assign role';
$string['checkout'] = 'Proceed to Checkout';
$string['cost'] = 'Enrol cost';
$string['costerror'] = 'The enrolment cost is not a number';
$string['crontask'] = 'Snipcart enrolment scheduled tasks';
$string['currency'] = 'Currency';
$string['currencyformat'] = 'Format';
$string['currencyerror'] = 'The currency is already used in this course.';
$string['customfieldpostcode'] = 'Postcode';
$string['defaultrole'] = 'Default role assignment';
$string['defaultrole_desc'] = 'Select role which should be assigned to users during Snipcart enrolments';
$string['enrolenddate'] = 'End date';
$string['enrolenddate_help'] = 'If enabled, users can be enrolled until this date only.';
$string['enrolenddaterror'] = 'Enrolment end date cannot be earlier than start date';
$string['enrolperiod'] = 'Enrolment duration';
$string['enrolperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['enrolperiod_help'] = 'Length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited.';
$string['enrolstartdate'] = 'Start date';
$string['enrolstartdate_help'] = 'If enabled, users can be enrolled from this date onward only.';

$string['eventsnipcartordercompleted'] = 'Snipcart order completed';
$string['eventsnipcartordercancelled'] = 'Snipcart order cancelled';

$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['nocost'] = 'There is no cost associated with enrolling in this course.';
$string['ordercomplete'] = 'Order Complete';
$string['ordercancelled'] = 'Thank you for your order, it has been cancelled. Your course enrolments purchased on this order have also been cancelled.';
$string['orderdisputed'] = 'Thank you for your order, it\'s currently being disputed. We\'ll email you when the dispute is resolved';
$string['orderitem'] = 'Courses Purchased';
$string['orderpending'] = 'Thank you for your order, it\'s currently being processed. We\'ll email you when it\'s done.';
$string['orderprice'] = 'Price';
$string['orderthankyou'] = 'Thank you for your order. Your invoice has been sent to you by email, you should receive it soon.';
$string['ordertotal'] = 'Total paid';
$string['pluginname'] = 'Snipcart';
$string['pluginname_desc'] = 'The Snipcart module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.';
$string['privateapikey'] = 'Private API Key';
$string['privateapikey_desc'] = 'The secret keys are used to access all the data of your Snipcart account. This key should never be visible to anyone that you did not allow to, do not use it as a public key on your website. Those keys give access to our RESTful API.';
$string['publicapikey'] = 'Public API Key';
$string['publicapikey_desc'] = 'The public API key is the key you need to add on your website. This key can be shared without security issues because it only allows a specific subset of API operations.';
$string['socialenrolments'] = 'Allow public (social) student enrolments';
$string['snipcartaccounts'] = 'Snipcart accounts';
$string['snipcartaccounts_desc'] = 'Configure the Snipcart accounts used for each country (currency)';
$string['snipcart:config'] = 'Configure Snipcart enrol instances';
$string['snipcart:manage'] = 'Manage enrolled users';
$string['snipcart:unenrol'] = 'Unenrol users from course';
$string['snipcart:unenrolself'] = 'Unenrol self from the course';
$string['status'] = 'Allow Snipcart enrolments';
$string['status_desc'] = 'Allow users to use Snipcart to enrol into a course by default.';