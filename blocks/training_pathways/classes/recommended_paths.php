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
 * block_training_pathways class that contains the recommended Training Pathways
 *
 * @package   block_training_pathways
 * @author    Tim Butler
 * @copyright 2017 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_training_pathways;

class recommended_paths {
    
    public  $paths;
    
    public function __construct() {
        global $DB, $USER;
        if ($this->paths == null) {
            // The user must have a region and role.
            if ($USER->profile['region'] == null || $USER->profile['role'] == null) {
                return;
            }
            
            // Get all Training Pathways for the user's region and role
            // that they aren't enrolled in.
            $sql = 'SELECT * FROM {block_training_pathways} btp
                    WHERE btp.regions LIKE ?
                    AND btp.course NOT IN
                        (SELECT courseid FROM {enrol} e
                        JOIN {user_enrolments} ue
                        ON e.id = ue.enrolid
                        WHERE ue.userid = ?)';
            $params = array( $USER->profile['region'], $USER->id );
            $results = $DB->get_records_sql($sql,$params);
            $this->paths = $results;
        }
        
    }
    
    public function view_paths() {
        if (empty($this->paths)) {
            return false;
        }
        
        $return = '';
        foreach($this->paths as $path) {
            $return.= $path->name;
        }
        
        return $return;
    }
}