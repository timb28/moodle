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

global $DB, $PAGE, $USER, $CFG;
$manage = new \mod_courseduration\manage($DB);

if (isset($_POST['action']) && $_POST['action'] == 'coursetimer_countdown') {
    $coursetimlength = $_POST['coursetimlength'];
    $result = coursetimercountdown($manage);
    $status = 'success';
    $msg = 'Course Timer Decrease Successfully';
    echo json_encode(array(
                        'status' => $status,
                        'code' => 200,
                        'msg' => $result->availabletime
                        ));
        exit;
}

function msg($type, $msg) {
    return '
                <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                '. $msg .'
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
            ';
}

