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

/* Report email contents */
require_once($CFG->dirroot . '/blocks/istart_reports/lang/en/manager_report.php');