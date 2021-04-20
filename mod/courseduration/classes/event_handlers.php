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

namespace mod_courseduration;
use mod_duration\manage;

use core\event\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Event handlers.
 *
 * This class contains all the event handlers used by Course Duration.
 *
 * @package   mod_courseduration
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class event_handlers {

    /**
     * The course view event.
     *
     * @param event $event
     * @return void
     */
    public static function all_events(base $event) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/courseduration/lib.php');

        $manage = new \mod_courseduration\manage();
        $manage->preparepage();
    }
}
