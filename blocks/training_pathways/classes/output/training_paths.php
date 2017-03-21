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

namespace block_training_pathways\output;

class training_paths implements \renderable, \templatable {
    
    protected $paths;
    
    public function __construct() {
        global $DB, $USER;
        if ($this->paths == null) {
            // The user must have a region and role.
            if ($USER->profile['region'] == null || $USER->profile['role'] == null) {
                return;
            }
            
            // Get all Training Pathways for the user's region and role
            $sql = 'SELECT
                        tp.id,
                        c.fullname,
                        c.summary,
                        tp.informationurl,
                        tp.course
                    FROM {block_training_pathways} tp
                        JOIN
                        {course} c ON tp.course = c.id
                    WHERE tp.regions LIKE ?
                    AND tp.roles LIKE ?';
            $params = array( $USER->profile['region'], $USER->profile['role'] );
            $results = $DB->get_records_sql($sql,$params);
            $this->paths = $results;
        }

    }
    
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @return stdClass
     */
    //public function export_for_template(\block_training_pathways_renderer $output) {
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();
        $data->enrolledpaths    = array();
        $data->recommendedpaths = array();
        
        if (empty($this->paths)) {
            return $data;
        }
        
        // Get all enrolled courses.
        $enrolledcourses = enrol_get_my_courses('id');
        
        foreach ($this->paths as $path) {

            // Sort enrolled paths from recommended paths 
            if (array_key_exists($path->course, $enrolledcourses)) {
                $data->enrolledpaths[$path->id]['id']                   = $path->id;
                $data->enrolledpaths[$path->id]['name']                 = $path->fullname;
                $data->enrolledpaths[$path->id]['description']          = $path->summary;
                $data->enrolledpaths[$path->id]['infourl']              = $path->informationurl;
                $data->enrolledpaths[$path->id]['courseurl']            = new \moodle_url('/course/view.php',
                        array('id' => $path->course));
                list($data->enrolledpaths[$path->id]['progresswidth'], 
                        $data->enrolledpaths[$path->id]['subcourses'])  = $this->get_subcourses($enrolledcourses[$path->course]);
            } else {
                $data->recommendedpaths[$path->id]['id']                = $path->id;
                $data->recommendedpaths[$path->id]['name']              = $path->fullname;
                $data->recommendedpaths[$path->id]['description']       = $path->summary;
                $data->recommendedpaths[$path->id]['infourl']           = $path->informationurl;
                $data->recommendedpaths[$path->id]['courseurl']         = new \moodle_url('/course/view.php',
                        array('id' => $path->course));
            }

        }
 
        return $data;
    }
    
    /**
     * Gets information on pathway courses via mod_subcourse instances.
     * 
     * @param type $course
     * @return ArrayIterator
     */
    private function get_subcourses($course) {
        $subcourses = array();
        
        $cms = get_fast_modinfo($course);
        $instances = $cms->get_instances_of('subcourse');
        foreach ($instances as $cminfo) {
            // Don't list instances that are not visible or available to the user.
            if ($cminfo->uservisible && $cminfo->available) {
                $subcourses[$cminfo->id]['name']    = $cminfo->get_formatted_name();
                $subcourses[$cminfo->id]['url']     = $cminfo->url;
                $subcourses[$cminfo->id]['course']  = $cminfo->url->get_param('id');
            }
        }

        // Calculate the width of the progress bars
        $subcoursewidth = (count($subcourses) > 1 ? 100 / count($subcourses) . '%' : '0%');

        return array($subcoursewidth, new \ArrayIterator($subcourses));
    }

}