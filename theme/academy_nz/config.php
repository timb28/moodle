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
 * @package   theme_academy_nz
 * @copyright 2011 Harcourts International Pty Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'academy_nz';

////////////////////////////////////////////////////
// Name of the theme.
////////////////////////////////////////////////////


$THEME->parents = array(
    'academy',
    'canvas',
    'base',
);

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