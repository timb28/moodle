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
 * @package mod_gradedtask
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_gradedtask_activity_task
 */

/**
 * Define the complete gradedtask structure for backup, with file and id annotations
 */
class backup_gradedtask_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $gradedtask = new backup_nested_element('gradedtask', array('id'), array(
            'name', 'intro', 'introformat', 'maxgrade', 'timemodified'));

        // Build the tree
        // (love this)

        // Define sources
        $gradedtask->set_source_table('gradedtask', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none)

        // Define file annotations
        $gradedtask->annotate_files('mod_gradedtask', 'intro', null); // This file area hasn't itemid

        // Return the root element (gradedtask), wrapped into standard activity structure
        return $this->prepare_activity_structure($gradedtask);
    }
}
