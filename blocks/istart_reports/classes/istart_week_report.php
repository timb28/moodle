<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace block_istart_reports;

require_once($CFG->dirroot . '/blocks/istart_reports/lib.php');

/**
 * Description of istart_week
 *
 * @author timbutler
 */
class istart_week_report {
    
    public  $reporttype,
            $reporttime,
            $course,
            $totalweeks,
            $istartgroups,
            $istartweeks;
    
    public function __construct($course, $reporttype, $reporttime) {
        $this->reporttype = $reporttype;
        $this->reporttime = $reporttime;
        $this->course = $course;
        $this->setup_totalweeks($course->id);
        $this->setup_istartgroups($course->id);

    } // _construct

    private function setup_totalweeks($courseid) {
        global $DB;

        // Get total number of istart weeks
        try {

            $sql = '
                    SELECT
                        MAX(CAST(cfo.value AS UNSIGNED)) as totalweeks
                    FROM
                        {course_format_options} AS cfo
                    WHERE
                        cfo.courseid = :courseid
                            AND cfo.name = :format_option_name';
            $params = array(
                            'courseid' => $courseid,
                            'format_option_name'  => 'istartweek');
            $record = $DB->get_record_sql($sql, $params, MUST_EXIST);

        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
            return("iStart manager report not sent because the total iStart weeks cannot be read from the database.");
        }

        $this->totalweeks = $record->totalweeks;
    }

    private function setup_istartgroups($courseid) {
        if (!isset($this->reporttime)) {
            return;
        }

        $allgroups = groups_get_all_groups($courseid);

        foreach ($allgroups as $group) {
            $istartgroup = new istart_group($group, $this->reporttime);
            if ($istartgroup->isvalidgroup) {
                $this->istartgroups[] = $istartgroup;
            }
        }
    }

    /**
    * Sends istart manager reports for a given istart intake group
    * @param stdClass $course The istart course object.
    * @param stdClass $group The group to process.
    * @return TODO true if a report was sent
    */
    public function process_manager_reports() {
        if ($this->reporttype !== MANAGERREPORTTYPE) {
            return;
        }

        // Send out all unsent manager reports from the last NUMPASTREPORTDAYS days.
        // Reports older than NUMPASTREPORTDAYS will not be mailed.  This is to avoid the problem where
        // cron has not been running for a long time or a student moves iStart group,
        // and then suddenly people are flooded with mail from the past few weeks or months
        foreach ($this->istartgroups as $istartgroup) {

            // Skip groups who have finished iStart
            if ($istartgroup->reportweeknum > $this->totalweeks) {
                error_log(" - 2. Skipping group who have completed iStart: ".$istartgroup->group->id.
                        " (".$istartgroup->group->name.") iStart week: " . $istartgroup->reportweeknum);
                continue;
            }

            // TODO remove testing code below
            error_log(" - 2. Started processing group: ".$istartgroup->group->id." (".$istartgroup->group->name.") iStart report week: " . $istartgroup->reportweeknum);
            error_log("   - group start date: " . date("Y-m-d", $istartgroup->startdate));
            error_log("   - group report week: " . $istartgroup->reportweeknum);

            $reportsendtime = $istartgroup->startdate + ($istartgroup->reportweeknum * WEEKSECS) + DAYSECS;

            error_log("   - group report send date: " . date("Y-m-d", $reportsendtime));

            if (date("Ymd", $reportsendtime) == date("Ymd", $this->reporttime)) {
                error_log(" - 3. Sending report today");

                // Get all group users
                $istartgroup->prepare_for_group_report();

                error_log(print_r($istartgroup->istartweek, 1));

                // Check if reports for those users have been sent
                $this->send_manager_report_to_group($istartgroup);
            }

        }

        return true;
   }

    /**
     * Sends an istart user a manager report for a given date.
     * @param int $courseid course ID of the istart course.
     * @param int $groupid ID of the user's group.
     * @param stdClass $user user being reported on.
     * @param string $istartweek The istart week.
     * @return true or error
     */
    function send_manager_report_to_group($istartgroup) {

        $istartusers = $istartgroup->istartusers;

        foreach ($istartusers as $istartuser) {
           $user    = $istartuser->user;
           $group   = $istartgroup->group;
           error_log(" - Sending manager report for $user->id at $this->reporttime"); // TODO remove after testing

            // Check if already sent
            if (!$this->is_report_sent($group, $user, MANAGERREPORTTYPE, $this->reporttime)) {
                $this->send_manager_report_for_user($istartgroup, $istartuser);
            }



       }
    }
   
    private function is_report_sent ($group, $user, $reporttype, $reporttime) {
        global $DB;

        $reportsent = false;

        try {
            $reportsent = $DB->record_exists_select('block_istart_reports',
                    'courseid = :courseid AND groupid = :groupid AND userid = :userid'
                    . ' AND reporttype = :reporttype AND reporttime = :reporttime AND senttime IS NOT NULL',
                         array(
                            'courseid' => $group->courseid,
                            'groupid'  => $group->id,
                            'userid'   => $user->id,
                            'reporttype' => $reporttype,
                            'reporttime' => $reporttime) );
        } catch(Exception $e) {
            error_log($e, DEBUG_NORMAL);
        }

        return $reportsent;
    }

    private function send_manager_report_for_user($istartgroup, $istartuser) {
        // Get all the user's managers
        $managers = $istartuser->managers;

        if(!isset($managers)) {
            // 'No managers for user: $user->id ($user->firstname $user->lastname).';
            return false;
        }

        foreach ($managers as $manager) {
            // Does the user have a manager's email address set?
            $manageremailaddress = $manager->email;
            if ($manageremailaddress == NULL) {
                $user = $istartuser->user;
                // 'Manager email is not set for user: $user->id ($user->firstname $user->lastname).';
                return false;
            }

            // Is the manager's email address valid?
            if (!validate_email($manageremailaddress)) {
                $user = $istartuser->user;
                // 'Manager email ($manageremailaddress) not valid for user:' . ' $user->id ($user->firstname $user->lastname).';
                return false;
            }

            return $this->send_manager_report_to_manager($istartgroup, $istartuser, $manager);
        }
    }

    private function send_manager_report_to_manager ($istartgroup, $istartuser, $manager) {
        
        error_log("   - To the manager: " . $manager->email);

        $group = $istartgroup->group;
        $user = $istartuser->user;

        // Create the email to send
        $email = new \stdClass();

        $reportdate = new \DateTime();
        $reportdate->setTimestamp($this->reporttime);

        $email->customheaders   = $this->get_email_headers(MANAGERREPORTTYPE, $this->reporttime, $group, $user);
        $email->subject         = $this->get_email_subject(MANAGERREPORTTYPE, $istartgroup, $user);
        $email->text            = $this->get_email_text($istartgroup, $istartuser);
        $email->html            = $this->get_email_html($istartgroup, $istartuser);

//        foreach ($tasksections as $tasksection) {
//            // Get the total number of tasks for each section and the
//            // total number of tasks the student has completed
//
//
//
//            error_log("   - Task sections: " . $tasksection->sectionid
//                    . ": " . $tasksection->sectionname
//                    . ": " . $tasksection->numtasks); // TODO remove after testing
//
//
//        }
    }

    private function get_email_headers($emailtype, $reporttime, $group, $user) {
        global $CFG;

        // Create the email headers
        $urlinfo = parse_url($CFG->wwwroot);
        $hostname = $urlinfo['host'];
        $course = $this->course;

        $customheaders = array();

        switch ($emailtype) {
            case MANAGERREPORTTYPE:
                $customheaders = array (  // Headers to make emails easier to track
                    'Return-Path: <>',
                    'List-Id: "iStart Manager Report" <istart.manager.report@'.$hostname.'>',
                    'List-Help: '.$CFG->wwwroot.'/course/view.php?id='.$course->id,
                    'Message-ID: <'.hash('sha256','Course: '.$course->id.' Group: '.$group->id
                            .' User: '.$user->id.' Report date: '.$reporttime).'@'.$hostname.'>',
                    'X-Course-Id: '.$course->id,
                    );
                break;
        }

        return $customheaders;
    }

    private function get_email_subject($emailtype, $istartgroup, $user) {
        $emailsubject = '';

        switch ($emailtype) {
            case MANAGERREPORTTYPE:
                // Create the email subject "iStart24 Online [Week #] completion report for [Firstname] [Lastname]"

                $istartweek = $istartgroup->istartweek;

                $a = new \stdClass();
                $a->istartweeknumber = $istartweek->weeknumber;
                $a->firstname = $user->firstname;
                $a->lastname = $user->lastname;
                $emailsubject =  get_string("manageremailsubject", "block_istart_reports", $a);
                break;
        }

        return $emailsubject;
    }

    private function get_email_text($istartgroup, $istartuser) {
        $course         = $this->course;
        $istartweek     = $istartgroup->istartweek;
        $tasksections   = $istartweek->tasksections;
        $user           = $istartuser->user;

        if (!isset($tasksections)) {
            return '';
        }

        // Create the email body
        // Add welcome message
        $a = new \stdClass();
        $a->coursename = $course->fullname;
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        $a->istartweeknumber = $istartweek->weeknumber;
        $a->istartweekname = $istartweek->weekname;

        $email = get_string('managerreporttextheader','block_istart_reports', $a);
        foreach ($tasksections as $tasksection) {
            $numtasks = $tasksection->numtasks;
            $numtaskscomplete = $istartuser->get_num_tasks_complete($tasksection->sectionid);

            $percentcomplete = 0;
            if ($numtasks > 0) {
                $percentcomplete = ceil( ($numtaskscomplete / $numtasks) * 100);
            }
            $graph = ceil($percentcomplete / 10);

            $a->graph = $graph;
            $a->sectionname = $tasksection->sectionname;
            $a->percentcomplete = $percentcomplete;
            $email .= get_string('managerreporttextbody','block_istart_reports', $a);
        }
        $email .= get_string('managerreporttextfooter','block_istart_reports', $a);
        unset($a);

        return $email;
    }

    private function get_email_html($istartgroup, $istartuser) {
        $course         = $this->course;
        $istartweek     = $istartgroup->istartweek;
        $tasksections   = $istartweek->tasksections;
        $user           = $istartuser->user;

        if (!isset($tasksections)) {
            return '';
        }

        // Create the email body
        // Add welcome message
        $a = new \stdClass();
        $a->coursename = $course->fullname;
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        $a->istartweeknumber = $istartweek->weeknumber;
        $a->istartweekname = $istartweek->weekname;

        $email = get_string('managerreporthtmlheader','block_istart_reports', $a);
        foreach ($tasksections as $tasksection) {
            $numtasks = $tasksection->numtasks;
            $numtaskscomplete = $istartuser->get_num_tasks_complete($tasksection->sectionid);

            $percentcomplete = 0;
            if ($numtasks > 0) {
                $percentcomplete = ceil( ($numtaskscomplete / $numtasks) * 100);
            }
            $graph = ceil($percentcomplete / 10);

            $a->graph = $graph;
            $a->sectionname = $tasksection->sectionname;
            $a->percentcomplete = $percentcomplete;
            $email .= get_string('managerreporthtmlbody','block_istart_reports', $a);
        }
        $email .= get_string('managerreporthtmlfooter','block_istart_reports', $a);

        return $email;
    }
}
