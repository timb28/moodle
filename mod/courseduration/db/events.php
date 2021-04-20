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
 * mod_courseduration event handlers used to display course timers.
 *
 * @package    mod_courseduration
 */
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'     => '\core\event\course_viewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 1000,
        'internal'      => false,
        ),
    array(
        'eventname'     => '\core\event\course_module_viewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 1000,
        'internal'      => false,
        ),
    array(
        'eventname'     => '\mod_quiz\event\course_module_viewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 1000,
        'internal'      => false,
    ),
    array(
        'eventname'     => '\mod_quiz\event\attempt_started',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 1000,
        'internal'      => false,
    ),
    array(
        'eventname'     => '\mod_quiz\event\attempt_viewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 1000,
        'internal'      => false,
    ),
    array(
        'eventname'     => '\mod_quiz\event\attempt_summary_viewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 10000,
        'internal'      => false,
    ),
    array(
        'eventname'     => '\mod_quiz\event\attempt_reviewed',
        'callback'      => '\mod_courseduration\event_handlers::all_events',
        'priority'      => 10000,
        'internal'      => false,
    )
);
