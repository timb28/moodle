<?php

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
 * Academy web services local plugin external functions and service definitions.
 *
 * @package    local_academywebservces
 * @copyright  2013 Harcourts International Pty Ltd (http://harcourts.net)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'academy_get_course_complete_for_user' => array(
                'classname'   => 'local_academywebservices_external',
                'methodname'  => 'get_course_complete_for_user',
                'classpath'   => 'local/academywebservices/externallib.php',
                'description' => 'Get course completion for a single user',
                'type'        => 'read',
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
/*$services = array(
        'Academy web service' => array(
                'functions' => array ('local_wstemplate_hello_world'),
                'restrictedusers' => 0,
                'enabled'=>1,
        )
);*/
