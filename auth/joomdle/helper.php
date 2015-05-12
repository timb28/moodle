<?php

function joomdle_wrapper_course_get_url ($course)
{
	$joomla_url = get_config ('auth/joomdle', 'joomla_url');
	return $joomla_url . "/index.php?option=com_joomdle&view=wrapper&moodle_page_type=course&id=".$course->id;
}

?>
