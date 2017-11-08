<?php

/**
 * Functions to support the Academy Library block
 *
 * @package   block_academy_library
 * @copyright 2011 onwards Harcourts International
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function block_academy_library_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload) {
    global $SCRIPT;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    require_course_login($course);

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_academy_library', 'content', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    session_get_instance()->write_close();
    send_stored_file($file, 60*60, 0, $forcedownload);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  $search
 * @param  $replace
 * @return void
 */
function block_academy_library_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'academy_library'));
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed
        $config = unserialize(base64_decode($instance->configdata));
        if (isset($config->text) and is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $instance->id));
        }
    }
    $instances->close();
}