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
 * Course renderer.
 *
 * @package    theme_noanme
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_academyboost\output\core;
defined('MOODLE_INTERNAL') || die();

use cm_info;
use core_text;
use core_course_renderer;
use html_writer;

require_once("$CFG->libdir/resourcelib.php");

/**
 * Course renderer class.
 *
 * @package    theme_academyboost
 * @copyright  2017 Harcourts International Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_renderer extends \theme_boost\output\core\course_renderer {

    /**
     * Renders html to display the videofile player instead of the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Other modules appear as normal.
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = array()) {
        global $CFG;

        /* Use a custom renderer for mod/videofile activities. */
        if ($mod->modname == "videofile") {
            return $this->videofile_renderer($mod, $displayoptions);
        }
        
        return core_course_renderer::course_section_cm_name($mod, $displayoptions);
    }
    
    private function videofile_renderer(cm_info $mod, $displayoptions = array()) {
        
        /* Non-embeded video files should also appear as normal. */
        $customdata = $mod->customdata;
        $display = $customdata['display'];
        if ($display != RESOURCELIB_DISPLAY_EMBED) {
            return core_course_renderer::course_section_cm_name($mod, $displayoptions);
        }

        $output = '';
        $embedvideofile = true;

        if (!$mod->uservisible && empty($mod->availableinfo)) {
            // nothing to be displayed to the user
            return $output;
        }
        $url = $mod->url;
        if (!$url) {
            return $output;
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        // For items which are hidden but available to current user
        // ($mod->uservisible), we show those as dimmed only if the user has
        // viewhiddenactivities, so that teachers see 'items which might not
        // be available to some students' dimmed but students do not see 'item
        // which is actually available to current student' dimmed.
        $linkclasses = '';
        $accesstext = '';
        $textclasses = '';

        if ($mod->uservisible) {
            $conditionalhidden = $this->is_cm_conditionally_hidden($mod);
            $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
                has_capability('moodle/course:viewhiddenactivities',
                        context_course::instance($mod->course));
            if ($accessiblebutdim) {
                $linkclasses .= ' dimmed';
                $textclasses .= ' dimmed_text';
                $embedvideofile = false;
                if ($conditionalhidden) {
                    $linkclasses .= ' conditionalhidden';
                    $textclasses .= ' conditionalhidden';
                }
                // Show accessibility note only if user can access the module himself.
                $accesstext = get_accesshide(get_string('hiddenfromstudents').':'. $mod->modfullname);
            }
        } else {
            $linkclasses .= ' dimmed';
            $textclasses .= ' dimmed_text';
            $embedvideofile = false;
        }

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        $groupinglabel = '';
        if (!empty($mod->groupingid) && has_capability('moodle/course:managegroups', context_course::instance($mod->course))) {
            $groupings = groups_get_all_groupings($mod->course);
            $groupinglabel = html_writer::tag('span', '('.format_string($groupings[$mod->groupingid]->name).')',
                    array('class' => 'groupinglabel '.$textclasses));
        }

        // Display link itself.
        if ($embedvideofile) {
            /* Resize the videofile container to suit the video dimensions. */
            $paddingbottom = round (100 / ($customdata['width'] / $customdata['height']), 2);

            $output.= html_writer::tag('h4', $instancename . $altname, array('class' => $textclasses));
            $output.= html_writer::start_div('videofile-container', array('style' => 'padding-bottom: '. $paddingbottom .'%'));
            $output.= html_writer::tag('iframe', '', array('width' => '560',
                                                             'height' => '315',
                                                             'src' => $url,
                                                             'frameborder' => '0',
                                                             'scrolling' => 'no',
                                                             'allowfullscreen' => '1'));

            $output.= html_writer::end_div();
        }

        if ($embedvideofile) {
            // Include the standard text and link for use by YUI
            $output.= html_writer::start_div('', array('style' => 'display: none;'));
        }
        
        $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
                'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')) . $accesstext .
                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
        if ($mod->uservisible) {
            $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick)) .
                    $groupinglabel;
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->uservisible)
            $output .= html_writer::tag('div', $activitylink, array('class' => $textclasses)) .
                    $groupinglabel;
        }

        if ($embedvideofile) {
            $output.= html_writer::end_div();
        }

        return $output;
    }

}
