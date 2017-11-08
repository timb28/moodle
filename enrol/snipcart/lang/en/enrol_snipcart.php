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
$string['addtocart'] = '{$a->cost} Add to cart';
$string['addedtocart'] = '{$a->cost} Added. <a href="#" onmousedown="javascript:Snipcart.api.showCart();" class="snipcart-checkout" >View cart</a>';
$string['assignrole'] = 'Assign role';
$string['copyright'] = '© Copyright Harcourts International';
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
$string['messageprovider:snipcart_enrolment'] = 'Snipcart Enrolment Notification';
$string['nocost'] = 'There is no cost associated with enrolling in this course.';
$string['nojavascript'] = 'Please follow these <a href="http://www.enable-javascript.com/" target="_blank">instructions to enable JavaScript in your web browser</a>.';
$string['ordercomplete'] = 'Order Complete';
$string['ordercancelled'] = 'Thank you for your order, it has been cancelled. Your course enrolments purchased on this order have also been cancelled.';
$string['orderdisputed'] = 'Thank you for your order, it\'s currently being disputed. We\'ll email you when the dispute is resolved';
$string['orderitem'] = 'Courses Purchased';
$string['orderpending'] = 'Thank you for your order, it\'s currently being processed. We\'ll email you when it\'s done.';
$string['orderprice'] = 'Price';
$string['orderthankyou'] = 'Thank you for your order. Your invoice has been sent to you by email.';
$string['ordertotal'] = 'Total paid';
$string['paymentrequired'] = 'Buy now for instant access.';
$string['pluginname'] = 'Snipcart';
$string['pluginname_desc'] = 'The Snipcart module allows you to set up paid courses.  If the cost for any course is zero, then students are not asked to pay for entry.';
$string['privateapikey'] = 'Private API Key';
$string['publicapikey'] = 'Public API Key';
$string['socialenrolments'] = 'Allow public (social) student enrolments';
$string['snipcartaccounts'] = 'Snipcart accounts';
$string['snipcartaccounts_desc'] = 'Configure the Snipcart accounts used for each country (currency)';
$string['snipcartinvalidorderror'] = 'Cannot get invalid Snipcart order. token: {$a->token} enrolid: {$a->currency}';
$string['snipcart:config'] = 'Configure Snipcart enrol instances';
$string['snipcart:manage'] = 'Manage enrolled users';
$string['snipcart:unenrol'] = 'Unenrol users from course';
$string['snipcart:unenrolself'] = 'Unenrol self from the course';
$string['status'] = 'Allow Snipcart enrolments';
$string['status_desc'] = 'Allow users to use Snipcart to enrol into a course by default.';

// Email contents

$string['email_ordercompletesubject']      = 'Start your Academy Real Estate Training courses now';
$string['email_ordercompleteheading']      = 'Thanks for registering.';
$string['email_ordercompletesubheading']   = 'Your online training is ready to start.';
$string['email_ordercompleteinvoice']      = 'Your purchase invoice is attached to a separate order confirmation email. If it hasn\'t arrived, please check your junk or spam email folders.';
$string['email_aboutus']                   = 'We provide real estate training in class and online for all roles including office administrators, property managers, sales consultants, managers, business owners and more.';
$string['email_findus']                    = '<a href="http://www.harcourtsacademy.com/">Find us online</a>';
$string['email_footer']                    = 'This email was sent to you because you purchased from our online store.';
$string['email_mailingaddressheader']      = 'Our mailing address is:';
$string['email_mailingaddress']            = '31 Amy Johnson Place Eagle Farm, QLD 4009 Australia';

$string['email_ordercompletetext']              = '
Hi {$a->firstname}

Thanks for registering.

Your online training is ready to start.
{$a->textcourselinks}

Your purchase invoice is attached to a separate order confirmation email.
If it hasn\'t arrived, please check your junk or spam email folders.

-------------------------------------------------------------------

We provide real estate training in class and online for all roles
including office administrators, property managers,
sales consultants, managers, business owners and more.

                          Find us online
               (http://www.harcourtsacademy.com/)
-------------------------------------------------------------------

(c) Copyright Harcourts International

This email was sent to you because you purchased from our online store.

Our mailing address is:
31 Amy Johnson Place
Eagle Farm, QLD 4009
Australia';