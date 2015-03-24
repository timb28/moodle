<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_istart_reports', language 'en'
 *
 * @package   block_istart_reports
 * @copyright Harcourts Academy <academy@harcourts.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['crontask'] = 'iStart Report jobs';
$string['descfoo'] = 'Config description';
//$string['blockconfigheader'] = 'iStart Reports Configuration';
//$string['blockconfigtotalweeks'] = 'Number of iStart Weeks';
$string['managerconfigdesc'] = 'Enter information included in manager report emails';
$string['emailcopyrightconfig'] = 'Email copyright';

$string['studentmanagerreports'] = 'Your weekly progress reports are being sent to:';
$string['labeleditreportaddress'] = 'Change';
$string['labelnewreportaddress'] = 'Select your manager';

$string['headermanageremail'] = 'Edit Your Manager';
$string['intromanager'] = 'Select your manager below to send them a weekly email report highlighting your iStart progress.';
$string['labelmanageremail'] = 'Manager&rsquo;s email address';
$string['labelcurrentmanager'] = 'Your current manager: ';
$string['labelselectmanager'] = 'Select a new manager: ';
$string['nomanager'] = '(No manager)';
$string['invalidmanager'] = 'Please select a different manager';
$string['candidatemanagers'] = 'Harcourts Team Members';
$string['candidatemanagersmatching'] = 'Matching Harcourts Team Members';
$string['existingmanagers'] = 'Your current managers';
$string['existingmanagersmatching'] = 'Matching managers';
$string['backtocourse'] = 'Back to iStart';

require_once($CFG->dirroot . '/blocks/istart_reports/lang/en/manager_report.php');

$string['labelfoo'] = 'Config label';
$string['noblockid'] = 'Couldn\'t find block id. Ensure this block exists in the block database table.';
$string['nocourse'] = 'Invalid Course with id of {$a}';
$string['istart_reports:addinstance'] = 'Add a istart_reports block';
$string['istart_reports:reporttomanager'] = 'Progress reported to a manager';
$string['pluginname'] = 'iStart Reports';

/* Event Logging */
$string['eventmanageradded'] = 'A student\'s manager has been assigned.';
