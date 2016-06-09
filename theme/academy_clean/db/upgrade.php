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

defined('MOODLE_INTERNAL') || die();

/**
 * theme_academy_theme upgrade function.
 *
 * @param  int $oldversion The version we upgrade from.
 * @return bool
 */
function xmldb_theme_academy_clean_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes.

    if ($oldversion < 2016060900) {

        // Define table theme_completionnotification to be created.
        $table = new xmldb_table('course_completion_notifs');

        // Adding fields to table theme_completionnotification.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursecompletionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timenotified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table theme_completionnotification.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('fk_coursecompletionid', XMLDB_KEY_FOREIGN, array('coursecompletionid'), 'course_completions', array('id'));

        // Conditionally launch create table for theme_completionnotification.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Academy_clean savepoint reached.
        upgrade_plugin_savepoint(true, 2016060900, 'theme', 'academy_clean');
    }
}