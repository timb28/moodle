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
 * Moodle's Clean theme, an example of how to make a Bootstrap theme
 *
 * DO NOT MODIFY THIS THEME!
 * COPY IT FIRST, THEN RENAME THE COPY AND MODIFY IT INSTEAD.
 *
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_academy_clean
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates a course buttons (completed|in progress|add to cart for course category listings
 *
 * @param stdClass $course to create buttons for
 * @return string The button HTML
 */
function theme_academy_create_course_button($course) {
    global $CFG, $DB, $USER;
    
    // Display button to open MNet remote course
    if ($course->id < 0 && !empty($course->remoteid)) {
        $viewcourseurl = $course->wwwroot . '/course/view.php?id=' . $course->remoteid;
        
        $inprogressbutton = get_string('inprogress', 'theme_academy_clean', $viewcourseurl);
        $completebutton = get_string('complete', 'theme_academy_clean', $viewcourseurl);
        
        if ($course->complete) {
            return $completebutton;
        }

        return $inprogressbutton;
    }
    
    require_once($CFG->libdir.'/completionlib.php');
    
    $instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'status'=>ENROL_INSTANCE_ENABLED), 'sortorder,id');
    
    foreach ($instances as $instance) {
        if ($instance->status != ENROL_INSTANCE_ENABLED or $instance->courseid != $course->id) {
            debugging('Invalid instances parameter submitted in theme_academy_create_course_button()');
            continue;
        }

        // Check if use is enrolled via this enrolment instance.
        if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id, 'status'=>ENROL_USER_ACTIVE))) {
            
            $viewcourseurl = new moodle_url('/course/view.php', array('id'=>$course->id));
            $inprogressbutton = get_string('inprogress', 'theme_academy_clean', $viewcourseurl->out());

            if ($CFG->enablecompletion != COMPLETION_ENABLED) {
                return $inprogressbutton;
            }

            $completebutton = get_string('complete', 'theme_academy_clean', $viewcourseurl->out());

            $coursecompletion = new completion_info($course);

            if ($coursecompletion->is_course_complete($USER->id)) {
                return $completebutton;
            } else {
                return $inprogressbutton;
            }
        }
    }
    
    /* Display any Snipcart 'Add to cart' buttons if the course is for sale and the user isn't already enrolled. */
    foreach ($instances as $instance) {
        if ($instance->enrol == 'snipcart' && $instance->status == ENROL_INSTANCE_ENABLED ) {
            $snipcart = enrol_get_plugin('snipcart');
            return $snipcart->get_add_to_cart_button($USER, $course, $instance, 'pull-right');
        }
    }
}

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_academy_clean_process_css($css, $theme) {

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_academy_clean_set_customcss($css, $customcss);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_academy_clean_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'logo') {
        $theme = theme_config::load('academy_clean');
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_academy_clean_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_academy_clean_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    $return->heading = $output->page_heading();

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.$page->theme->settings->footnote.'</div>';
    }
    
    $return->piwik = '';
    if (!empty($page->theme->settings->piwiksiteid)) {
        $return->piwik = '
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["trackPageView"]);
  _paq.push(["enableLinkTracking"]);

  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://www.academyrealestatetraining.com/webstats/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", "'.$page->theme->settings->piwiksiteid.'"]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Piwik Code -->';
    }

    return $return;
}

/**
 * Returns the next page module in a course section. Used to create 'Next' buttons in iStart.
 *
 * @param int|stdClass $courseorid object from DB table 'course' (must have field 'id'
 *     and recommended to have field 'cacherev') or just a course id. Just course id
 *     is enough when calling get_fast_modinfo() for current course or site or when
 *     calling for any other course for the second time.
 * @param int $section Section number - from course_sections table
 * @param int $module Course-module ID - from course_modules table
 * @return modinfo|null Module information for next page module, or null if none found
 */
function theme_academy_clean_get_next_page_in_section($courseorid, $section, $module) {
    // Get all the modules in the current section
    $modinfo = get_fast_modinfo($courseorid);
    $sectionmods = $modinfo->sections[$section];
    if (is_array($sectionmods)) {

        // limit the array of mods to those after the current mod
        $key = array_search($module, $sectionmods);
    } else {
        return null;
    }

    if ($key+1 < count($sectionmods)) {
        // Create an array with only those mods after the current mod
        // in this course section
        $nextmods = array_slice($sectionmods, $key+1);
    }

    if (!empty($nextmods)) {

        foreach ($nextmods as $modnumber) {
            $nextmod = get_fast_modinfo($courseorid)->cms[$modnumber];
            if ($nextmod->modname == 'page' and $nextmod->visible and $nextmod->available) {
                // We have found our next page
                $nextpagemod = $nextmod;
                break;
            }
        }
    }
    
    return !empty($nextpagemod) ? $nextpagemod : null;
}

/**
 * Deprecated: Please call theme_academy_clean_process_css instead.
 * @deprecated since 2.5.1
 */
function academy_clean_process_css($css, $theme) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_academy_clean_process_css($css, $theme);
}

/**
 * Deprecated: Please call theme_academy_clean_set_customcss instead.
 * @deprecated since 2.5.1
 */
function academy_clean_set_customcss($css, $customcss) {
    debugging('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__, DEBUG_DEVELOPER);
    return theme_academy_clean_set_customcss($css, $customcss);
}
