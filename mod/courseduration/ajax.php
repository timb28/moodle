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
 * Capabilities for category access plugin.
 *
 * @package    mod_courseduration
 */
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once('lib.php');

global $PAGE, $USER, $CFG;
$manage = new \mod_courseduration\manage();

if (isset($_POST['action']) && $_POST['action'] == 'coursetimer_countdown') {
    $courseid = (int) $_POST['courseid'];
    $coursetimer = (int) $_POST['coursetimer'];
    $coursetimerlength = (int) $_POST['coursetimerlength']; // in millliseconds
    $coursetimerupdated = (int) $_POST['coursetimerupdated']; // in milliseconds

    error_log(" +++ AJAX CT called:" . print_r($coursetimer, true));
    try {
        $result = $manage->updatecoursetimer($courseid, $coursetimer, $coursetimerlength, $coursetimerupdated);
        $status = 'success';
        $code = 200;
        $msg = 'Course Timer Updated';
    } catch (coding_exception $e) {
        $result = 0;
        $status = 'error';
        $code = 500;
        $msg = 'Course Timer Unchanged';
    } catch (dml_exception $e) {
        $result = 0;
        $status = 'error';
        $code = 500;
        $msg = 'Course Timer Unchanged';
    }
    echo json_encode(array(
                        'result' => $result,
                        'status' => $status,
                        'code' => $code,
                        'msg' => $msg
                        ));
        exit;
}

