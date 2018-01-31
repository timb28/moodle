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
 * Class containing data for my overview block.
 *
 * @package    block_myoverview
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myoverview\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_completion\progress;
use context_helper; // Academy Patch M#061

require_once($CFG->dirroot . '/blocks/myoverview/lib.php');
require_once($CFG->libdir . '/accesslib.php'); // Academy Patch M#061
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/coursecatlib.php'); // Academy Patch M#061

/**
 * Class containing data for my overview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * @var string The tab to display.
     */
    public $tab;

    /**
     * Constructor.
     *
     * @param string $tab The tab to display.
     */
    public function __construct($tab, $sortby, $searchcriteria) { // Academy Patch M#061
        $this->tab = $tab;
        $this->sortby = $sortby; // Academy Patch M#061
        $this->searchcriteria = $searchcriteria;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;

        /* START Academy Patch M#061 My Overview block customisations. */
        if ($this->sortby == BLOCK_MYOVERVIEW_SORT_DEFAULT) {
            if (empty($CFG->navsortmycoursessort)) {
                $sort = 'visible DESC, sortorder ASC';
            } else {
                $sort = 'visible DESC, '.$CFG->navsortmycoursessort.' ASC';
            }

            $courses = enrol_get_my_courses('*', $sort);
        } elseif ($this->sortby == BLOCK_MYOVERVIEW_SORT_ALPHA) {
            $sort = 'visible DESC, fullname ASC';
            $courses = enrol_get_my_courses('*', $sort);
        } elseif ($this->sortby == BLOCK_MYOVERVIEW_SORT_ACCESSED) {
            // Sort order is BLOCK_MYOVERVIEW_SORT_ACCESSED
            $courses = $this->enrol_get_my_courses_by_lastaccessed('*', 'visible DESC, sortorder ASC');
        }

        if (empty($this->searchcriteria['search'])) {
            $options = array('recursive' => true,
                             'sort' => array('id' => -1));
            $coursecat = \coursecat::get('1');
            $allcourses = $coursecat->get_courses($options);
        } else {
            error_log('searching : ' . print_r($this->searchcriteria, true));
            $allcourses = $this->search_courses($this->searchcriteria);
        }

        /* END Academy Patch M#061 */
        $coursesprogress = [];

        foreach ($courses as $course) {

            $completion = new \completion_info($course);

            // First, let's make sure completion is enabled.
            if (!$completion->is_enabled()) {
                continue;
            }

            $percentage = progress::get_course_progress_percentage($course);
            if (!is_null($percentage)) {
                $percentage = floor($percentage);
            }

            $coursesprogress[$course->id]['completed'] = $completion->is_course_complete($USER->id);
            $coursesprogress[$course->id]['progress'] = $percentage;
        }

        $coursesview = new courses_view($allcourses, $courses, $coursesprogress); // Academy Patch M#061
        $nocoursesurl = $output->image_url('courses', 'block_myoverview')->out();
        $noeventsurl = $output->image_url('activities', 'block_myoverview')->out();

        // Now, set the tab we are going to be viewing.
        /* START Academy Patch M#061 My Overview block customisations. */
        $viewingallcourses = false;
        $viewingcourses = false;
        if ($this->tab == BLOCK_MYOVERVIEW_ALLCOURSES_VIEW) {
            $viewingallcourses = true;
        } else {
            $viewingcourses = true;
        }
        /* END Academy Patch M#061 My Overview block customisations. */

        return [
            'midnight' => usergetmidnight(time()),
            'coursesview' => $coursesview->export_for_template($output),
            'urls' => [
                'nocourses' => $nocoursesurl,
                'noevents' => $noeventsurl
            ],
            'viewingallcourses' => $viewingallcourses, // Academy Patch M#061
            'viewingcourses' => $viewingcourses,
            'searchcriteria' => $this->searchcriteria
        ];
    }

    /** START Academy Patch M#061 My Overview block customisations.
     * Returns list of courses current $USER is enrolled in and can access
     * Adapted from lib/enrollib::enrol_get_my_courses()
     *
     * - $fields is an array of field names to ADD
     *   so name the fields you really need, which will
     *   be added and uniq'd
     *
     * @param string|array $fields
     * @param string $sort
     * @param int $limit max number of courses
     * @param array $courseids the list of course ids to filter by
     * @return array
     */
   function enrol_get_my_courses_by_lastaccessed($fields = null, $sort = 'DESC',
                                 $limit = 0, $courseids = []) {
       global $DB, $USER;

       // Guest account does not have any courses
       if (isguestuser() or !isloggedin()) {
           return(array());
       }

       $basefields = array('id', 'category', 'sortorder',
                           'shortname', 'fullname', 'idnumber',
                           'startdate', 'visible',
                           'groupmode', 'groupmodeforce', 'cacherev');

       if (empty($fields)) {
           $fields = $basefields;
       } else if (is_string($fields)) {
           // turn the fields from a string to an array
           $fields = explode(',', $fields);
           $fields = array_map('trim', $fields);
           $fields = array_unique(array_merge($basefields, $fields));
       } else if (is_array($fields)) {
           $fields = array_unique(array_merge($basefields, $fields));
       } else {
           throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
       }
       if (in_array('*', $fields)) {
           $fields = array('*');
       }

       $orderby = "ORDER BY la.timeaccess DESC";
       $sort    = trim($sort);
       if (!empty($sort) && $sort == 'ASC') {
           $orderby = "ORDER BY la.timeaccess ASC";
       }

       $wheres = array("c.id <> :siteid");
       $params = array('siteid'=>SITEID);

       if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) {
           // list _only_ this course - anything else is asking for trouble...
           $wheres[] = "courseid = :loginas";
           $params['loginas'] = $USER->loginascontext->instanceid;
       }

       $coursefields = 'c.' .join(',c.', $fields);
       $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
       $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
       $params['contextlevel'] = CONTEXT_COURSE;
       $wheres = implode(" AND ", $wheres);

       if (!empty($courseids)) {
           list($courseidssql, $courseidsparams) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
           $wheres = sprintf("%s AND c.id %s", $wheres, $courseidssql);
           $params = array_merge($params, $courseidsparams);
       }

       //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
       $sql = "SELECT $coursefields $ccselect, la.timeaccess
                 FROM {course} c
                 JOIN (SELECT DISTINCT e.courseid
                         FROM {enrol} e
                         JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                        WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                      ) en ON (en.courseid = c.id)
                 JOIN {user_lastaccess} la ON c.id = la.courseid AND la.userid = :userid2
              $ccjoin
                WHERE $wheres
             $orderby";
       $params['userid']  = $USER->id;
       $params['userid2'] = $USER->id;
       $params['active']  = ENROL_USER_ACTIVE;
       $params['enabled'] = ENROL_INSTANCE_ENABLED;
       $params['now1']    = round(time(), -2); // improves db caching
       $params['now2']    = $params['now1'];

       $courses = $DB->get_records_sql($sql, $params, 0, $limit);

       // preload contexts and check visibility
       foreach ($courses as $id=>$course) {
           context_helper::preload_from_record($course);
           if (!$course->visible) {
               if (!$context = context_course::instance($id, IGNORE_MISSING)) {
                   unset($courses[$id]);
                   continue;
               }
               if (!has_capability('moodle/course:viewhiddencourses', $context)) {
                   unset($courses[$id]);
                   continue;
               }
           }
           $courses[$id] = $course;
       }

       //wow! Is that really all? :-D

       return $courses;
   }

   /** 
     * Returns list of courses current $USER can access
     * Adapted from lib/coursecat::search_courses()
     *
     * @return array
     */
   function search_courses(array $searchcriteria) {
       global $PAGE;

        // Trigger event, courses searched.
        $eventparams = array('context' => $PAGE->context, 'other' => array('query' => $searchcriteria['search']));
        $event = \core\event\courses_searched::create($eventparams);
        $event->trigger();

        $coursesinlist = \coursecat::search_courses($this->searchcriteria);



        $coursefields = array('id','category','sortorder','fullname','shortname','idnumber','summary','summaryformat','format','showgrades','newsitems','startdate','enddate','marker','maxbytes','legacyfiles','showreports','visible','visibleold','groupmode','groupmodeforce','defaultgroupingid','lang','theme','timecreated','timemodified','requested','enablecompletion','completionnotify','cacherev','calendartype');

        foreach ($coursesinlist as $courseinlist) {
            if ($courseinlist instanceof stdClass) {
                $courseinlist = new course_in_list($courseinlist);
            }

            if (!$courseinlist->can_access()) {
                continue;
            }

            $course = new \stdClass();
            foreach ($coursefields as $field) {
                $course->$field = $courseinlist->$field;
            }
            $courses[$course->id] = $course;
        }

        return $courses;
   }
   /* END Academy Patch M#061 */
}
