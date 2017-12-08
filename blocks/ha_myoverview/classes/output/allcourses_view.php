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
 * Class containing data for courses view in the ha_myoverview block.
 *
 * @package    block_ha_myoverview
 * @copyright  2017 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_ha_myoverview\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use core_course\external\course_summary_exporter;

/**
 * Class containing data for courses view in the ha_myoverview block.
 *
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class allcourses_view implements renderable, templatable {
    /** Quantity of courses per page. */
    const COURSES_PER_PAGE = 6;

    /** @var array $courses List of courses the user can be or is enrolled in. */
    protected $courses = [];

    /** @var array $coursesprogress List of progress percentage for each course. */
    protected $coursesprogress = [];

    /**
     * The allcourses_view constructor.
     *
     * @param array $courses list of courses.
     * @param array $coursesprogress list of courses progress.
     */
    public function __construct($courses, $coursesprogress) {
        $this->courses = $courses;
        $this->coursesprogress = $coursesprogress;
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
        $allcoursesview = [
            'hascourses' => !empty($this->courses)
        ];

        // How many courses we have per status?
        $coursesbystatus = ['notenrolled' => 0 ,'past' => 0, 'inprogress' => 0, 'future' => 0];
        foreach ($this->courses as $course) {
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

            $courseprogress = null;

            $classified = course_classify_for_timeline($course);

            if (isset($this->coursesprogress[$courseid])) {
                $courseprogress = $this->coursesprogress[$courseid]['progress'];
                $exportedcourse->hasprogress = !is_null($courseprogress);
                $exportedcourse->progress = $courseprogress;
            }

            if ($classified == COURSE_TIMELINE_PAST) {
                // Courses that have already ended.
                $pastpages = floor($coursesbystatus['past'] / $this::COURSES_PER_PAGE);

                $allcoursesview['past']['pages'][$pastpages]['courses'][] = $exportedcourse;
                $allcoursesview['past']['pages'][$pastpages]['active'] = ($pastpages == 0 ? true : false);
                $allcoursesview['past']['pages'][$pastpages]['page'] = $pastpages + 1;
                $allcoursesview['past']['haspages'] = true;
                $coursesbystatus['past']++;
            } else if ($classified == COURSE_TIMELINE_FUTURE) {
                // Courses that have not started yet.
                $futurepages = floor($coursesbystatus['future'] / $this::COURSES_PER_PAGE);

                $allcoursesview['future']['pages'][$futurepages]['courses'][] = $exportedcourse;
                $allcoursesview['future']['pages'][$futurepages]['active'] = ($futurepages == 0 ? true : false);
                $allcoursesview['future']['pages'][$futurepages]['page'] = $futurepages + 1;
                $allcoursesview['future']['haspages'] = true;
                $coursesbystatus['future']++;
            } else {
                // Courses still in progress. Either their end date is not set, or the end date is not yet past the current date.
                $inprogresspages = floor($coursesbystatus['inprogress'] / $this::COURSES_PER_PAGE);

                $allcoursesview['inprogress']['pages'][$inprogresspages]['courses'][] = $exportedcourse;
                $allcoursesview['inprogress']['pages'][$inprogresspages]['active'] = ($inprogresspages == 0 ? true : false);
                $allcoursesview['inprogress']['pages'][$inprogresspages]['page'] = $inprogresspages + 1;
                $allcoursesview['inprogress']['haspages'] = true;
                $coursesbystatus['inprogress']++;
            }
        }

        // Build courses view paging bar structure.
        foreach ($coursesbystatus as $status => $total) {
            $quantpages = ceil($total / $this::COURSES_PER_PAGE);

            if ($quantpages) {
                $allcoursesview[$status]['pagingbar']['disabled'] = ($quantpages <= 1);
                $allcoursesview[$status]['pagingbar']['pagecount'] = $quantpages;
                $allcoursesview[$status]['pagingbar']['first'] = ['page' => '&laquo;', 'url' => '#'];
                $allcoursesview[$status]['pagingbar']['last'] = ['page' => '&raquo;', 'url' => '#'];
                for ($page = 0; $page < $quantpages; $page++) {
                    $allcoursesview[$status]['pagingbar']['pages'][$page] = [
                        'number' => $page + 1,
                        'page' => $page + 1,
                        'url' => '#',
                        'active' => ($page == 0 ? true : false)
                    ];
                }
            }
        }

        return $allcoursesview;
    }
}
