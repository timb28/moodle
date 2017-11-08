<?php

/**
 * iStart Reports block strings
 *
 * @package   block_istart_reports
 * @author    Tim Butler
 * @copyright 2015 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* Block strings */
$string['blockhasmanagerintro']  = 'Your weekly progress reports are being sent to:';
$string['blockhasmanageraction'] = 'Change';
$string['blocknomanagerintro'] = 'Select your manager to send them a weekly email report highlighting your iStart progress.';
$string['blocknomanageraction'] = 'Select your manager';

/* Managers form */
$string['headermanagerform'] = 'Edit Your Manager';
$string['intromanagerform'] = '<strong>Search</strong> then <strong>Add</strong> your manager(s) below to send them a weekly email report highlighting your iStart progress.';
$string['labelcurrentmanager'] = 'Your current manager(s): ';
$string['existingmanagers'] = 'Your current manager(s)';
$string['existingmanagersmatching'] = 'Matching managers';
$string['labelselectmanager'] = 'Add a new manager: ';
$string['candidatemanagers'] = 'Harcourts Team Members';
$string['candidatemanagersmatching'] = 'Matching Harcourts Team Members';
$string['backtocourse'] = 'Back to iStart';

/* Standard strings */
$string['crontask'] = 'iStart Report scheduled tasks';
$string['noblockid'] = 'Couldn\'t find block id. Ensure this block exists in the block database table.';
$string['nocourse'] = 'Invalid Course with id of {$a}';
$string['istart_reports:addinstance'] = 'Add a istart_reports block';
$string['istart_reports:reporttomanager'] = 'Progress reported to a manager';
$string['pluginname'] = 'iStart Reports';

/* Event logging */
$string['eventmanageradded'] = 'A student\'s manager has been added.';
$string['eventmanagerremoved'] = 'A student\'s manager has been removed.';
$string['eventmanagerreportsent'] = 'An iStart manager report has been sent.';

/**
 * Manager Welcome Email strings
 */

$string['managerwelcomeemailsubject']   = 'iStart24 Progress Reports for {$a->firstname} {$a->lastname} start soon';
$string['managerwelcometextheader']     = 'Your weekly iStart24 Progress Report for {$a->firstname} {$a->lastname} starts soon

Great news! {$a->firstname} {$a->lastname} is completing iStart24 Online,
the 24 week coaching programme for real estate sales. They have selected
you as their manager who is keeping them accountable for completing their
iStart24 tasks each week.

A progress report for {$a->firstname} will be emailed to you weekly.
We recommend referring to the report during your weekly
one-on-one meetings.

For more information, refer to our iStart24 Managers Guide.
(http://www.harcourtsacademy.com/courses/sales/istart24/istart24-managers-guide)
';

$string['managerwelcometextfooter'] = '
------------------------------------------------------------
Each iStart24 week is structured to include a focus on a particular
aspect of the real estate business. There are four parts to every week.

1. Video to watch
2. Content to read
3. Forum to share
4. Tasks to do

-------------------------------------------------------------------
 Find out more
 (http://www.harcourtsacademy.com/sales/istart24-online)
-------------------------------------------------------------------

Copyright Harcourts International, All rights reserved.

This email was sent to you because {$a->firstname} {$a->lastname}
nominated you as their manager.

Our mailing address is:
31 Amy Johnson Place
Eagle Farm, QLD 4009
Australia';

$string['managerwelcomehtmlheaderintro']    = 'iStart Progress Report';
$string['managerwelcomehtmlheading']        = 'Report for {$a->firstname} {$a->lastname}';
$string['managerwelcomehtmlcontent']        = 'Great news! {$a->firstname} {$a->lastname} is completing iStart24 Online,
    the 24 week coaching programme for real estate sales. They have selected you as their manager who is keeping them
    accountable for completing their iStart24 tasks each week.<br>
    <br>
    A progress report for {$a->firstname} will be emailed to you weekly. We recommend referring to the report during
    your weekly one-on-one meetings.<br>
    <br>
    For more information, refer to our <a href="http://www.harcourtsacademy.com/courses/sales/istart24/istart24-managers-guide" target="_blank" style="word-wrap: break-word;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%;color: #6DC6DD;font-weight: normal;text-decoration: underline;">iStart24 Managers Guide</a>.
';


/**
 * Manager Welcome Report strings
 */
$string['managerreportemailsubject']    = 'iStart24 Online Week {$a->istartweeknumber} Progress Report for {$a->firstname} {$a->lastname}';

$string['managerreporttextheader'] = '
{$a->coursename} Progress Report for {$a->firstname} {$a->lastname}
{$a->istartweekname}
------------------------------------------------------------
';

$string['managerreporttextbody'] = '
%{$a->percentcomplete} of {$a->sectionname} tasks complete
';

$string['managerreporttextfooter'] = '
------------------------------------------------------------
Each iStart24 week is structured to include a focus on a particular
aspect of the real estate business. There are four parts to every week.

1. Video to watch
2. Content to read
3. Forum to share
4. Tasks to do

-------------------------------------------------------------------
 Find out more
 (http://www.harcourtsacademy.com/sales/istart24-online)
-------------------------------------------------------------------

Copyright Harcourts International, All rights reserved.

This email was sent to you because {$a->firstname} {$a->lastname}
nominated you as their manager.

Our mailing address is:
31 Amy Johnson Place
Eagle Farm, QLD 4009
Australia';

$string['managerreporthtmltitle']       = 'iStart24 Online Week {$a->istartweeknumber} progress report for {$a->firstname} {$a->lastname}';
$string['managerreporthtmlheaderintro'] = '{$a->coursename} Progress Report for {$a->firstname} {$a->lastname}';
$string['managerreporthtmlheading']     = '{$a->coursename} Report for {$a->firstname} {$a->lastname}';
$string['managerreporthtmltasksummary'] = '{$a->percentcomplete}% of {$a->sectionname} tasks complete';
$string['managerreporthtmlistartinfo']  = 'Each iStart24 week is structured to include a focus on a particular aspect of the real estate business. '
                                        . 'There are four parts to every week.';
$string['managerreporthtmlwatchlabel']  = 'Video to watch';
$string['managerreporthtmlreadlabel']   = 'Content to read';
$string['managerreporthtmlconnectlabel']= 'Forum to share';
$string['managerreporthtmldolabel']     = 'Tasks to do';
$string['managerreporthtmlactionlabel'] = 'Find out more';
$string['managerreporthtmlactionurl']   = 'http://www.harcourtsacademy.com/sales/istart24-online';
$string['managerreporthtmlcopyright']   = 'Copyright © Harcourts International, All rights reserved.';
$string['managerreporthtmlreason']      = 'This email was sent to you because {$a->firstname} {$a->lastname} nominated you as their manager.';
$string['managerreporthtmladdress']     = '<strong>Our mailing address is:</strong><br>31 Amy Johnson Place<br>Eagle Farm, QLD 4009<br>Australia';