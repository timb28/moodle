<?php

/**
 * @package Joomdle
 * @copyright 2012 Antonio Duran
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/questiontypebase.php');



function question_preview_question_pluginfile_joomdle($course, $context, $component,
        $filearea, $qubaid, $slot, $filename, $forcedownload) {
    global $USER, $DB, $CFG;
          $query = "SELECT *
                FROM {$CFG->prefix}files
                WHERE component = 'question'
                AND filearea = ?
                AND itemid = ?
                AND filename = ?
                ORDER by id
                LIMIT 1";
        $params = array ($filearea, $qubaid, $filename);
        $record =  $DB->get_record_sql($query, $params);

    $fs = get_file_storage();

    if (!$file = $fs->get_file_by_hash($record->pathnamehash)) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload);

}


function question_pluginfile_joomdle($course, $context, $component, $filearea, $args, $forcedownload) {
    global $DB, $CFG;

    list($context, $course, $cm) = get_context_info_array($context->id);

    $qubaid = (int)array_shift($args);
    $filename = array_shift($args);

    $module = $DB->get_field('question_usages', 'component',
            array('id' => $qubaid));

	return question_preview_question_pluginfile_joomdle($course, $context,
			$component, $filearea, $qubaid, $slot, $filename, $forcedownload);
}

?>
