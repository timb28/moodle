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
 * courseduration external functions and service definitions.
 *
 * @package    mod_courseduration
 * @category   external
 * @copyright  2021 AB <virasatsolutions.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.3
 */

defined('MOODLE_INTERNAL') || die;

$functions = array(

    'mod_courseduration_get_coursedurations_by_courses' => array(
        'classname'     => 'mod_courseduration_external',
        'methodname'    => 'get_coursedurations_by_courses',
        'description'   => 'Returns a list of coursedurations in a provided list of courses',
        'type'          => 'read',
        'capabilities'  => 'mod/courseduration:view',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);
