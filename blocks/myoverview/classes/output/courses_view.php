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
 * Class containing data for courses view in the myoverview block.
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
use core_course\external\course_summary_exporter;
use theme_snap\local; // Academy Patch M#061
use core_tag_tag; // Academy Patch M#061
use core_tag\output\taglist; // Academy Patch M#061

/* START Academy Patch M#061 My Overview block customisations and M#065 Display remote MNet courses in block_myoverview. */
        
/**
 * Class containing data for courses view in the myoverview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 6;

    /** @var array $allcourses List of courses the user is able to access. */
    protected $allcourses = []; // Academy Patch M#061

    /** @var array $courses List of courses the user is enrolled in. */
    protected $courses = [];

    /** @var array $coursesprogress List of progress percentage for each course. */
    protected $coursesprogress = [];

    /**
     * The courses_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($allcourses, $courses, $coursesprogress) { // Academy Patch M#061
        $this->allcourses = empty($allcourses) ? array() : $allcourses; // Academy Patch M#061
        $this->courses = $courses;
        $this->coursesprogress = $coursesprogress; // Academy Patch M#061
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        // Build courses view data structure.
        $coursesview = [
            'hascourses' => !empty($this->courses)
        ];

        // How many courses we have per status?
        $coursesbystatus = ['all' => 0, 'past' => 0, 'inprogress' => 0, 'future' => 0];

        foreach ($this->allcourses as $course) {

            // MNet remote courses have negative id
            if ($course->id > 0) {
                $exportedcourse = $this->prepare_local_course($course, $output);
            }

            // All courses
            $pastpages = floor($coursesbystatus['all'] / $this::COURSES_PER_PAGE);

            $coursesview['all']['pages'][$pastpages]['courses'][] = $exportedcourse;
            $coursesview['all']['pages'][$pastpages]['active'] = ($pastpages == 0 ? true : false);
            $coursesview['all']['pages'][$pastpages]['page'] = $pastpages + 1;
            $coursesview['all']['haspages'] = true;
            $coursesbystatus['all']++;
        }

        foreach ($this->courses as $course) {
            // User is always enrolled in these courses.
            $course->isenrolled = true;

            // MNet remote courses have negative id
            if ($course->id > 0) {
                $exportedcourse = $this->prepare_local_course($course, $output);
                $classified = course_classify_for_timeline($course);
            } else {
                $exportedcourse = $this->prepare_remote_course($course);
                $classified = remote_course_classify_for_timeline($course);
            }

            if ($classified == COURSE_TIMELINE_PAST) {
                // Courses that have already ended.
                $pastpages = floor($coursesbystatus['past'] / $this::COURSES_PER_PAGE);

                $coursesview['past']['pages'][$pastpages]['courses'][] = $exportedcourse;
                $coursesview['past']['pages'][$pastpages]['active'] = ($pastpages == 0 ? true : false);
                $coursesview['past']['pages'][$pastpages]['page'] = $pastpages + 1;
                $coursesview['past']['haspages'] = true;
                $coursesbystatus['past']++;
            } else if ($classified == COURSE_TIMELINE_FUTURE) {
                // Courses that have not started yet.
                $futurepages = floor($coursesbystatus['future'] / $this::COURSES_PER_PAGE);

                $coursesview['future']['pages'][$futurepages]['courses'][] = $exportedcourse;
                $coursesview['future']['pages'][$futurepages]['active'] = ($futurepages == 0 ? true : false);
                $coursesview['future']['pages'][$futurepages]['page'] = $futurepages + 1;
                $coursesview['future']['haspages'] = true;
                $coursesbystatus['future']++;
            } else {
                // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
                $inprogresspages = floor($coursesbystatus['inprogress'] / $this::COURSES_PER_PAGE);

                $coursesview['inprogress']['pages'][$inprogresspages]['courses'][] = $exportedcourse;
                $coursesview['inprogress']['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
                $coursesview['inprogress']['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
                $coursesview['inprogress']['haspages'] = true;
                $coursesbystatus['inprogress']++;
            }
        }

        // Build courses view paging bar structure.
        foreach ($coursesbystatus as $status => $total) {
            $quantpages = ceil($total / $this::COURSES_PER_PAGE);

            if ($quantpages) {
                $coursesview[$status]['pagingbar']['disabled'] = ($quantpages <= 1);
                $coursesview[$status]['pagingbar']['pagecount'] = $quantpages;
                $coursesview[$status]['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
                $coursesview[$status]['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
                for ($page = 0; $page < $quantpages; $page++) {
                    $coursesview[$status]['pagingbar']['pages'][$page] = [
                        'number' => $page + 1,
                        'page' => $page + 1,
                        'url' => '#',
                        'active' => ($page == 0 ? true : false)
                    ];
                }
            }
        }

        return $coursesview;
    }

    /**
     * Prepare local course for export.
     *
     * @param stdClass $course
     * @param \renderer_base $output
     * @return stdClass
     */
    private function prepare_local_course($course, $output) {
        $courseid = $course->id;
        $context = \context_course::instance($courseid);
        $exporter = new course_summary_exporter($course, [
            'context' => $context
        ]);
        $exportedcourse = $exporter->export($output);

        // Convert summary to plain text.
        $exportedcourse->summary = content_to_text($exportedcourse->summary, $exportedcourse->summaryformat);

        // Include course visibility.
        $exportedcourse->visible = (bool)$course->visible;

        // Get the cover image.
        $exportedcourse->coverimageurl = \theme_snap\local::course_card_image_url($course->id);

        if ($course->isenrolled) {
            $exportedcourse->action = "Open";
        } else {
            $exportedcourse->action = get_string('more');
        }

        if ($course->isenrolled) {
            $exportedcourse->isenrolled = true;
        } else {
            $exportedcourse->isenrolled = false;
        }

        $taglist = new \core_tag\output\taglist(core_tag_tag::get_item_tags('core', 'course', $exportedcourse->id), null);
        $outputtaglist = $taglist->export_for_template($output);
        foreach ($outputtaglist->tags as $tag) {
            if (($strpos = strpos($tag->name, ":")) !== FALSE) {
                $type = trim(substr($tag->name, 0, $strpos));
                $value = trim(substr($tag->name, $strpos + 1));
            }
            switch ($type) {
                case 'duration':
                    $exportedcourse->duration = $value;
                    $exportedcourse->durationclass = preg_replace('/[\s:]+/','',$tag->name);
                    break;
                case 'experience':
                    $exportedcourse->experience = $value;
                    $exportedcourse->experienceclass = preg_replace('/[\s:]+/','',$tag->name);
                    break;
                case 'role':
                    $exportedcourse->role = $value;
                    $exportedcourse->roleclass = preg_replace('/[\s:]+/','',$tag->name);
                    break;
            }
        }

        $courseprogress = null;

        if (isset($this->coursesprogress[$courseid])) {
            $courseprogress = $this->coursesprogress[$courseid]['progress'];
            $exportedcourse->hasprogress = !is_null($courseprogress);
            $exportedcourse->progress = $courseprogress;
        }

        return $exportedcourse;
    }

    /**
     * Prepare MNet remote course for export.
     *
     * @param stdClass $course
     * @return stdClass
     */
    private function prepare_remote_course($course) {
        $courseid = $course->id * -1;

        $exportedcourse = new \stdClass();

        $exportedcourse->id                 = $courseid;
        $exportedcourse->fullname           = $course->fullname;
        $exportedcourse->fullnamedisplay    = $course->fullname;
        $exportedcourse->shortname          = $course->shortname;
        $exportedcourse->idnumber           = $course->idnumber;
        $exportedcourse->viewurl = (new \moodle_url($course->wwwroot . '/course/view.php', array('id' => $courseid)))->out(false);

        // Convert summary to plain text.
        $exportedcourse->summary            = content_to_text($course->summary, $course->summaryformat);
        $exportedcourse->summaryformat      = $course->summaryformat;

        $exportedcourse->startdate          = $course->startdate;
        $exportedcourse->enddate            = $course->enddate;

        // Include course visibility.
        $exportedcourse->visible = (bool)$course->visible;

        $exportedcourse->isenrolled = true;

        if ($course->enablecompletion) {
            $exportedcourse->hasprogress = $course->enablecompletion;
            $exportedcourse->progress = $course->progress;
        }

        return $exportedcourse;
    }
}
/* END Academy Patch M#061 and M#065 */
