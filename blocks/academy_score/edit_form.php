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
 * Academy Score Block.
 *
 * @package   block_academy_score
 * @author    Tim Butler <tim.butler@harcourts.net>, Karen Holland <kholland.dev@gmail.com>, Mei Jin, Jiajia Chen
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->libdir . '/coursecatlib.php');

class block_academy_score_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        // Section header according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
        
        
        
        $coursecategories = array();
        $categories = coursecat::get(0)->get_children();  // Parent = 0   ie top-level categories only
        if ($categories) {   //Check we have categories
            if (count($categories) > 1 || (count($categories) == 1)) {     // Just print top level category links
                foreach ($categories as $category) {
                    $categoryname = $category->get_formatted_name();
                    $coursecategories[$category->id] = $categoryname;
                }
            }
        }
        
        $attributes = "style=\"width:20em;height:20em;\"";
        $select = $mform->addElement('select', 'config_coursecategories', get_string('coursecategories'), $coursecategories, $attributes);
        $select->setMultiple(true);
        
        // This will select the skills A and B.
        // $mform->getElement('md_skills')->setSelected(array('val1', 'val2'));
    }
    
}

?>
