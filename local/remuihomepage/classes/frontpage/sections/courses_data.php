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

namespace local_remuihomepage\frontpage\sections;
defined('MOODLE_INTERNAL') || die();

use context_system;
use context_course;
use context_coursecat;
use core_course\external\course_summary_exporter as cme;
use \theme_remui\utility as utility;

if (!class_exists('core_course_category')) {
    require_once($CFG->libdir. '/coursecatlib.php');
}
trait courses_data {


    /**
     * Get all details of categories
     * @param  array $categories Array of categories
     * @return array             Array of categories with category details
     */
    private function get_category_details($categories) {
        global $OUTPUT, $CFG, $USER;
        $result = array();

        $activeflag = true;

        foreach ($categories as $key => $category) {
            $rescategories = array();
            if ($activeflag) {
                $rescategories['active'] = true;
                $activeflag = false;
            }
            if (class_exists('core_course_category')) {
                $category = \core_course_category::get($category, MUST_EXIST, true);
            } else if ('coursecat') {
                $category = \coursecat::get($category, MUST_EXIST, true);
            } else {
                continue;
            }

            if ( !$category->visible ) {

                $categorycontext = \context_coursecat::instance($category->id);
                if (!has_capability('moodle/category:viewhiddencategories', $categorycontext, $USER->id)) {
                    continue;
                }

                $rescategories['ishidden'] = true;
            }

            $rescategories['categoryid'] = $category->id;
            $rescategories['categoryname'] = strip_tags(format_text($category->name));
            $catsummary = strip_tags(format_text($category->description));
            $catsummary = preg_replace('/\n+/', '', $catsummary);
            $catsummary = strlen($catsummary) > 150 ? mb_substr($catsummary, 0, 150) . "..." : $catsummary;
            $rescategories['categorydesc'] = $catsummary;
            $rescategories['categoryurl'] = $CFG->wwwroot. '/course/index.php?categoryid=' . $category->id;
            $count = \theme_remui\utility::get_courses(true, null, $category->id, 0, 0, null, null);
            $rescategories['coursecount'] = $count;

            if ($category->idnumber != '') {
                $rescategories['idnumber'] = $category->idnumber;
            }

            if ($category->parent != "0") {
                if (class_exists('core_course_category')) {
                    $parent = \core_course_category::get($category->parent);
                } else if (class_exists('coursecat')) {
                    $parent = \coursecat::get($category->parent);
                } else {
                    continue;
                }
                $rescategories['parentname'] = $parent->name;
            }
            $result[] = $rescategories;
        }
        return $result;
    }

    /**
     * Get all courses from selected categories and date filter
     * @param  array  $categories Categories list
     * @param  string $date       Date filter
     * @return array              Array of course
     */
    public function get_courses_from_category($categories, $date, $start = 0) {
        global $OUTPUT, $CFG, $DB, $USER;

        $courses = array();

        // As going to call same function from course archive page...
        // which need this object to return the data.
        $pageobj = new \stdClass;
        $pageobj->courses = -1;

        $obj = new \stdClass;
        $obj->sort = null;
        $obj->search = "";
        $obj->tab = false;
        $obj->page = $pageobj;
        $obj->pagination = false;
        $obj->view = null;
        $obj->isFilterModified = "0";
        // We do not need the extra data for courses like instructor, canedit, etc.
        $obj->limiteddata = true;

        $fields = implode(', ', array(
            'c.id',
            'c.category',
            'c.fullname',
            'c.shortname',
            'c.startdate',
            'c.enddate',
            'c.visible',
            'c.sortorder'
        ));
        $where = '';
        // Get system course context.
        $coursecontext = context_course::instance(1);
        if (!has_capability('moodle/course:viewhiddencourses', $coursecontext, $USER->id) || !isloggedin()) {
            $where .= "AND c.visible = 1";
        }
        list($insql, $inparams) = $DB->get_in_or_equal($categories, SQL_PARAMS_NAMED);
        $sql = "SELECT $fields FROM {course} c WHERE c.category $insql $where";

        $courses = $DB->get_records_sql($sql, $inparams);

        $total = count($courses);
        $obj->courses = array_slice($courses, $start, 25);
        $obj->category = 0;
        $result = \theme_remui\utility::get_course_cards_content($obj, $date);
        return array($total, $result['courses']);
    }

    /**
     * Get categories list from user settings
     * @param  array $configdata Config data array
     * @return array             Array of categories
     */
    public function get_categories($categories) {
        global $DB;
        if (empty($categories)) {
            $categories = $DB->get_records('course_categories', null, '', 'id', 0, 0);
            return array_keys($categories);
        }
        foreach ($categories as $category) {
            $cats = \theme_remui\utility::get_allowed_categories($category);
            foreach ($cats as $cat) {
                if (!in_array($cat, $categories)) {
                    array_push($categories, $cat);
                }
            }
        }
        if (!empty($categories)) {
            return $categories;
        }
        return [];
    }

    /**
     * Returns the courses form.
     * @param  stdClass &config data object
     * @return stdClass config data
     */
    private function courses_data($configdata) {
        global $CFG, $OUTPUT, $DB;
        // Multi Lang support for Main Tile, Description and btnlabel.
        if (isset($configdata['title']['text'])) {
            $configdata['title']['text'] = strip_tags(format_text($configdata['title']['text']));
        }
        if (isset($configdata['description']['text'])) {
            $configdata['description']['text'] = strip_tags(format_text($configdata['description']['text']));
        }
        $result = [];
        $categories = $this->get_categories(isset($configdata['categories']) ? $configdata['categories'] : []);
        $date = isset($configdata['date']) ? $configdata['date'] : 'all';

        $type = $configdata['show'];
        switch ($type) {
            case 'courses':
                $configdata['courseviewlink'] = $CFG->wwwroot.'/course/view.php?id=';
                list($configdata['totalcourses'], $configdata['courses']) = $this->get_courses_from_category($categories, $date);
                if (empty($configdata['courses'])) {
                    $configdata['coursesplaceholder'] = $OUTPUT->image_url('courses', 'block_myoverview')->out();
                }
                break;

            case 'categories':
                $configdata['categorylist'] = $this->get_category_details($categories);
                $configdata['totalcourses'] = 0;
                break;

            case 'categoryandcourses':
                $configdata['categoryandcourses'] = true;
                if (\theme_remui\toolbox::get_setting('enablenewcoursecards')) {
                    $configdata['latest_card'] = true;
                }
                $configdata['catlist'] = $this->get_category_details($categories);
                $configdata['totalcourses'] = 0;
                break;

            default:
                break;
        }
        $configdata[$type.'view'] = true;

        $configdata['sectionpropertiesoutput'] = str_replace(
            "class=\"", "class=\"" . $type . "-view ",
            $configdata['sectionpropertiesoutput']
        );

        return $configdata;
    }

}
