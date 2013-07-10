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
 * Config for the academy theme
 *
 * @package   theme_academy
 * @copyright 2011 Harcourts Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'academy';

////////////////////////////////////////////////////
// Name of the theme.
////////////////////////////////////////////////////

$THEME->doctype = 'html5';
$THEME->parents = array(
    'canvas',
    'base',
);

/////////////////////////////////////////////////////
// List exsisting theme(s) to use as parents.
////////////////////////////////////////////////////


$THEME->sheets = array(
    'academy',
    'joomla',
    'content',
    'ie',
    'print',
);

////////////////////////////////////////////////////
// Name of the stylesheet(s) you are including in
// this new theme's /styles/ directory.
////////////////////////////////////////////////////

$THEME->enable_dock = false;

////////////////////////////////////////////////////
// Do you want to use the new navigation dock?
////////////////////////////////////////////////////

$THEME->javascripts = array(
    'jquery.min',
    'jquery.hoverIntent.minified',
    'sitemenu'
);

////////////////////////////////////////////////////
// An array containing the names of JavaScript files
// located in /javascript/ to include in the theme.
// (gets included in the head)
////////////////////////////////////////////////////


$THEME->layouts = array(
    // Most pages - if we encounter an unknown or a missing page type, this one is used.
    'base' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post', 'bottom'),
        'defaultregion' => 'side-post'
    ),
    'standard' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post', 'bottom'),
        'defaultregion' => 'side-post'
    ),
    // Course page
    'course' => array(
        'file' => 'course.php',
        'regions' => array('center', 'side-post','bottom'),
        'defaultregion' => 'side-post'
    ),
    // Course page
    'coursecategory' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post', 'bottom'),
        'defaultregion' => 'side-post'
    ),
    'incourse' => array(
        'file' => 'incourse.php',
        'regions' => array('center','side-post','bottom'),
        'defaultregion' => 'side-post'
    ),
    'frontpage' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post', 'bottom'),
        'defaultregion' => 'side-post'
    ),
    'admin' => array(
        'file' => 'admin.php',
        'regions' => array('center', 'side-post', 'bottom'),
        'defaultregion' => 'side-post'
    ),
    'mydashboard' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'mypublic' => array(
        'file' => 'general.php',
        'regions' => array('center', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'login' => array(
        'file' => 'login.php',
        'regions' => array()
    ),
    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'noblocks'=>true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter', 'noblocks'=>true),
    ),
    // Embeded pages, like iframe embeded in moodleform
    'embedded' => array(
        'theme' => 'canvas',
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true),
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, and it is good idea if it does not have links to
    // other places - for example there should not be a home link in the footer...
    'maintenance' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>true, 'noblocks'=>true),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>false, 'noblocks'=>true),
    ),
    'report' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter'=>true, 'nonavbar'=>false, 'noblocks'=>true),
    ),
);


///////////////////////////////////////////////////////////////
// These are all of the possible layouts in Moodle.
///////////////////////////////////////////////////////////////


$THEME->csspostprocess = 'academy_process_css';



///////////////////////////////////////////////////////////////
// Theme Specific settings for Administrators to customise
// css.
///////////////////////////////////////////////////////////////

$THEME->editor_sheets = array('editor');

////////////////////////////////////////////////////
// Overrides the left arrow image used throughout
// Moodle
////////////////////////////////////////////////////

$THEME->larrow    =  '&laquo;'; //'&lang;';

////////////////////////////////////////////////////
// Overrides the right arrow image used throughout Moodle
////////////////////////////////////////////////////

$THEME->rarrow    = '&raquo;'; //'&rang;';

// Sets a custom render factory to use with the theme, used when working with custom renderers.
$THEME->rendererfactory = 'theme_overridden_renderer_factory';