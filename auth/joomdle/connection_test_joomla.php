<?php

/**
 * @author Antonio Duran
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package joomdle
 */

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
//require_once($CFG->libdir.'/authlib.php');
require_once($CFG->dirroot.'/auth/joomdle/auth.php');

// it gives a warning if no context set, I guess it does nor matter which we use
$PAGE->set_context(context_system::instance());

$joomla_url = get_config (NULL, 'joomla_url');
$file_url = $joomla_url.'/components/com_joomdle/connection_test.php';

$auth = new auth_plugin_joomdle ();
echo $auth->get_file ($file_url);
