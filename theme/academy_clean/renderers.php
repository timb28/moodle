<?php

/**
 * A custom renderer for the Academy theme to produce customised content.
 *
 * @package    theme
 * @subpackage academy_clean
 * @copyright  Harcourts International Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

include_once($CFG->dirroot . "/blocks/course_overview/renderer.php");
include_once($CFG->dirroot . "/course/renderer.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("lib.php");

class theme_academy_clean_core_renderer extends theme_bootstrapbase_core_renderer {

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $this->page->navbar->prepend('Online Learning', 'http://'.$_SERVER['SERVER_NAME'].'/online-learning');
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
                /*new navigation_node(array(
            'text'=>'Online Learning',
            'shorttext'=>'Online Learning',
            'key'=>-1,
            'action'=>'/online-learning'
        )));*/
                
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">&gt;</span>';
        $list_items = '<li>'.join("$divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    }
}

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
    
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG, $PAGE;
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content = '';
        $classes = trim('coursebox clearfix '. $additionalclasses);
        if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $nametag = 'h3';
        } else {
            $classes .= ' collapsed';
            $nametag = 'div';
        }

        // .coursebox
        $content .= html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
        ));

        $content .= html_writer::start_tag('div', array('class' => 'info'));

        // course name
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                                            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::tag($nametag, $coursenamelink, array('class' => 'coursename'));

        // print 'add to cart' buttons
        if ($PAGE->context instanceof context_coursecat) {
            $content.= theme_academy_create_course_button($course);
        }

        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
        $content .= html_writer::start_tag('div', array('class' => 'moreinfo'));
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
                $url = new moodle_url('/course/info.php', array('id' => $course->id));
                $image = html_writer::empty_tag('img', array('src' => $this->output->pix_url('i/info'),
                    'alt' => $this->strings->summary));
                $content .= html_writer::link($url, $image, array('title' => $this->strings->summary));
                // Make sure JS file to expand course content is included.
                $this->coursecat_include_js();
            }
        }
        $content .= html_writer::end_tag('div'); // .moreinfo

        // print enrolmenticons
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pix_icon) {
                $content .= $this->render($pix_icon);
            }
            $content .= html_writer::end_tag('div'); // .enrolmenticons
        }
        
        $content .= html_writer::end_tag('div'); // .info

        $content .= html_writer::start_tag('div', array('class' => 'content'));
        $content .= $this->coursecat_coursebox_content($chelper, $course);
        $content .= html_writer::end_tag('div'); // .content

        $content .= html_writer::end_tag('div'); // .coursebox
        return $content;
    }

}

class theme_academy_clean_block_course_overview_renderer extends block_course_overview_renderer {
    
    /**
     * Construct contents of course_overview block
     *
     * @param array $courses list of courses in sorted order
     * @param array $overviews list of course overviews
     * @return string html to be displayed in course_overview block
     */
    public function course_overview($courses, $overviews) {
        $html = '';
        $config = get_config('block_course_overview');
        if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
            global $CFG;
            require_once($CFG->libdir.'/coursecatlib.php');
        }
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
            $userediting = true;
            $this->page->requires->js_init_call('M.block_course_overview.add_handles');

            // Check if course is moving
            $ismovingcourse = optional_param('movecourse', FALSE, PARAM_BOOL);
            $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
            // Remove movecourse param from url.
            $this->page->ensure_param_not_in_url('movecourse');

            // Show moving course notice, so user knows what is being moved.
            $html .= $this->output->box_start('notice');
            $a = new stdClass();
            $a->fullname = $courses[$movingcourseid]->fullname;
            $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
            $html .= get_string('movingcourse', 'block_course_overview', $a);
            $html .= $this->output->box_end();

            $moveurl = new moodle_url('/blocks/course_overview/move.php',
                        array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid));
            // Create move icon, so it can be used.
            $movetofirsticon = html_writer::empty_tag('img',
                    array('src' => $this->output->pix_url('movehere'),
                        'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                        'title' => get_string('movehere')));
            $moveurl = html_writer::link($moveurl, $movetofirsticon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }
            $html .= $this->output->box_start('coursebox', "course-{$course->id}");
            $html .= html_writer::start_tag('div', array('class' => 'course_title'));
            // If user is editing, then add move icons.
            if ($userediting && !$ismovingcourse) {
                $moveicon = html_writer::empty_tag('img',
                        array('src' => $this->pix_url('t/move')->out(false),
                            'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                            'title' => get_string('move')));
                $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
                $moveurl = html_writer::link($moveurl, $moveicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));

            }

            // No need to pass title through s() here as it will be done automatically by html_writer.
            $attributes = array('title' => $course->fullname);
            if ($course->id > 0) {
                if (empty($course->visible)) {
                    $attributes['class'] = 'dimmed';
                }
                $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
                $link = html_writer::link($courseurl, $coursefullname, $attributes);
                $html .= $this->output->heading($link, 2, 'title');
                /* START Academy code to show the course button on the My Moodle page. */
                // print 'add to cart' buttons
                $html .= theme_academy_create_course_button($course);
                /* END Academy code */
            } else {
                $html .= $this->output->heading(html_writer::link(
                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                    format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
            }
            $html .= $this->output->box('', 'flush');
            $html .= html_writer::end_tag('div');

            if (!empty($config->showchildren) && ($course->id > 0)) {
                // List children here.
                if ($children = block_course_overview_get_child_shortnames($course->id)) {
                    $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                }
            }

            // If user is moving courses, then down't show overview.
            if (isset($overviews[$course->id]) && !$ismovingcourse) {
                $html .= $this->activity_display($course->id, $overviews[$course->id]);
            }

            if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
                // List category parent or categories path here.
                $currentcategory = coursecat::get($course->category, IGNORE_MISSING);
                if ($currentcategory !== null) {
                    $html .= html_writer::start_tag('div', array('class' => 'categorypath'));
                    if ($config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                        foreach ($currentcategory->get_parents() as $categoryid) {
                            $category = coursecat::get($categoryid, IGNORE_MISSING);
                            if ($category !== null) {
                                $html .= $category->get_formatted_name().' / ';
                            }
                        }
                    }
                    $html .= $currentcategory->get_formatted_name();
                    $html .= html_writer::end_tag('div');
                }
            }

            $html .= $this->output->box('', 'flush');
            $html .= $this->output->box_end();
            $courseordernumber++;
            if ($ismovingcourse) {
                $moveurl = new moodle_url('/blocks/course_overview/move.php',
                            array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid));
                $a = new stdClass();
                $a->movingcoursename = $courses[$movingcourseid]->fullname;
                $a->currentcoursename = $course->fullname;
                $movehereicon = html_writer::empty_tag('img',
                        array('src' => $this->output->pix_url('movehere'),
                            'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                            'title' => get_string('movehere')));
                $moveurl = html_writer::link($moveurl, $movehereicon);
                $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
            }
        }
        // Wrap course list in a div and return.
        return html_writer::tag('div', $html, array('class' => 'course_list'));
    }
    
}

?>
