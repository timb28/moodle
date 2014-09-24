<?php
class block_academy_grades extends block_base {
    public function init() {
        $this->title = get_string('title', 'block_academy_grades');
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false);
    }

    public function get_content() {
        global $CFG, $DB, $OUTPUT;
        
        if ($this->content !== null) {
          return $this->content;
        }

        /* Count the number of activities in the course to determine whether the a link to view this courses
         * grades should be displayed.
         */
       $course = $this->page->course;

        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);

        $archetypes = array();

        $numactivities = 0;
        $temp = null;

        foreach($modinfo->cms as $cm) {
            // Exclude activities which are not visible or have no link (=label)
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }
            $numactivities++;
        }

        $textcontent = '<p>' . get_string('introduction', 'block_academy_grades') . '</p>';

        if ($numactivities > 0) {
            $userreporticon = '<img src="'.$OUTPUT->pix_url('i/one') . '" class="icon" alt="" />&nbsp;';
            $userreportlink = $CFG->wwwroot.'/grade/report/user/index.php?id='.$this->page->course->id;
            $textcontent.= '<p>' . $userreporticon . '<a href="' . $userreportlink . '">' . get_string('userreport', 'block_academy_grades') . '</a></p>';
        }

        $overviewreporticon = '<img src="'.$OUTPUT->pix_url('i/all') . '" class="icon" alt="" />&nbsp;';
        $overviewreportlink = $CFG->wwwroot.'/grade/report/overview/index.php?id='.$this->page->course->id;
        $textcontent.= '<p>' . $overviewreporticon . '<a href="' . $overviewreportlink . '">' . get_string('overviewreport', 'block_academy_grades') . '</a></p>';

        $this->content         =  new stdClass;
        $this->content->text   = $textcontent;

        return $this->content;
    }

}
?>
