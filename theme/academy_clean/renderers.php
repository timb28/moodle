<?php

/**
 * A custom renderer for the Academy theme to produce customised content.
 *
 * @package    theme
 * @subpackage academy_clean
 * @copyright  Harcourts International Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_academy_clean_core_renderer extends theme_bootstrapbase_core_renderer {

   /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $list_items = '<li>'.join("$divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }
}

include_once($CFG->dirroot . "/course/renderer.php");
require_once("$CFG->libdir/resourcelib.php");

class theme_academy_clean_core_course_renderer extends core_course_renderer {

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

        /* Modules other than mod/videofile appear as normal. */
        if ($mod->modname !== "videofile") {
            return core_course_renderer::course_section_cm_name($mod, $displayoptions);
        }

        /* Non-embeded video files should also appear as normal. */
        $customdata = $mod->customdata;
        $display = $customdata['display'];
        if ($display != RESOURCELIB_DISPLAY_EMBED) {
            return core_course_renderer::course_section_cm_name($mod, $displayoptions);
        }

        $output = '';
        $embedvideofile = true;

        if (!$mod->uservisible &&
                (empty($mod->showavailability) || empty($mod->availableinfo))) {
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
        if (false !== strpos(textlib::strtolower($instancename),
                textlib::strtolower($altname))) {
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

?>
