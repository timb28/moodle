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
 * A custom renderer class that extends the plugin_renderer_base and
 * is used by the training pathways block.
 *
 * @package   block_training_pathways
 * @author    Tim Butler
 * @copyright 2017 onwards Harcourts Academy {@link http://www.harcourtsacademy.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

namespace block_training_pathways\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {

    /**
     * Display the recommended training pathways using the template.
     *
     * @param block_training_pathways\output\training_paths $paths The recommended paths to display.
     * @return string
     */
    public function render_training_paths(\block_training_pathways\output\training_paths $paths) {
        $data = $paths->export_for_template($this, $this->target === RENDERER_TARGET_GENERAL);
        $return = '';
        if (!empty($data) && is_array($data->paths) && !empty($data->paths)) {
            $return.= '<div class="accordion recommended_paths" id="accordion2">';
            foreach ($data->paths as $path) {
                $return.= $this->render_from_template('block_training_pathways/recommended_paths', $path);
            }
            $return.= '</div>';
        }
        
        return $return;
    }

}
