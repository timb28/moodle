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
 *  Flowplayer media plugin filtering
 *
 *  This filter will replace any links to a media file with
 *  a FlowPlayer instance that plays that media inline
 *
 * @package    filter
 * @subpackage flowplayer
 * @copyright  2011 onwards Tim Butler  {@link http://harcourts.net}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');

class filter_flowplayer extends moodle_text_filter {
    function filter($text, array $options = array()) {
        global $CFG, $PAGE;
        // You should never modify parameters passed to a method or function, it's BAD practice. Create a copy instead.
        // The reason is that you must always be able to refer to the original parameter that was passed.
        // For this reason, I changed $text = preg_replace(..,..,$text) into $newtext = preg.... (NICOLAS CONNAULT)
        // Thanks to Pablo Etcheverry for pointing this out! MDL-10177

        // We're using the UFO technique for flash to attain XHTML Strict 1.0
        // See: http://www.bobbyvandersluis.com/ufo/
        if (!is_string($text)) {
            // non string data can not be filtered anyway
            return $text;
        }
        if (stripos($text, '</a>') === false) {
            // performance shortcut - all regexes bellow end with the </a> tag,
            // if not present nothing can match
            return $text;
        }
        $newtext = $text; // fullclone is slow and not needed here

        if (!empty($CFG->filter_flowplayer_enable_mp4)) {
            $search = '/<a(\s+[^>]+?)?\s+href="([^"]+\.mp4)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_flowplayer_mp4_callback', $newtext);
        }

        if (!empty($CFG->filter_flowplayer_enable_mp3)) {
            $search = '/<a(\s+[^>]+?)?\s+href="([^"]+\.mp3)(\?d=([\d]{1,4}%?)x([\d]{1,4}%?))?"[^>]*>.*?<\/a>/is';
            $newtext = preg_replace_callback($search, 'filter_flowplayer_mp3_callback', $newtext);
        }

        if (empty($newtext) or $newtext === $text) {
            // error or not filtered
            unset($newtext);
            return $text;
        }

        return $newtext;
    }
}

///===========================
/// callback filter functions

function filter_flowplayer_mp4_callback($link) {
    global $CFG, $OUTPUT, $PAGE;

    static $count = 0;
    $count++;
    $id = 'filter_flowplayer_mp4_'.time().$count; //we need something unique because it might be stored in text cache

    $url = addslashes_js($link[2]);
    $width = 720;
    $height = 404;

    if (isset($link[4])) {
        $width = $link[4];
    }

    if (isset($link[5])) {
        $height = $link[5];
    }

    if (isset($link[6])) {
        $thumbnailformat = $link[6];
    }

    $csswidth = $width . "px";
    $cssheight = $height . "px";

    $flowplayerswfpath = $CFG->wwwroot.'/filter/flowplayer/flowplayer-3.2.7.swf';
    $flowplayerjspath = $CFG->wwwroot.'/filter/flowplayer/flowplayer-3.2.6.min.js';
    $jquerypath = $CFG->wwwroot.'/filter/flowplayer/jquery.min.js';

    $output = <<<OET
    <script type="text/javascript" src="$flowplayerjspath"></script>
    <script type="text/javascript" src="$jquerypath"></script>
    <style type="text/css">
    video#video_$id, #player_$id {
            display:block;
            width:$csswidth;
            height:$cssheight;
    }
    </style>
    
    <a id="player_$id" href="$url"></a>

    <script language="JavaScript">
    flowplayer("player_$id", "$flowplayerswfpath", {
        clip: {
            autoPlay: false,
            autoBuffering: true,
            scaling: 'fit'
        }
    });
    </script>

    <video id="video_$id" width="$width" height="$height" controls poster="$url.jpg">
        <source src="$url" type="video/mp4">
    </video>

    <script type="text/javascript">
        $(document).ready(function() {
            var hasFlash = false;
            try {
                var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                if(fo) hasFlash = true;
            }catch(e){
                if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true;
            }

            //if browser has flash, remove the video tag
            if(hasFlash){
                $('#video_$id').remove();
            } else {
                // otherwise remove the video tag
                $('#player_$id').remove();
            }
        }); //end doc ready

</script>
OET;

    return $output;
}

function filter_flowplayer_mp3_callback($link) {
    global $CFG, $OUTPUT, $PAGE;

    static $count = 0;
    $count++;
    $id = 'filter_flowplayer_mp3_'.time().$count; //we need something unique because it might be stored in text cache

    $url = addslashes_js($link[2]);
    $width = 710;
    $height = 400;

    if (!is_null($link[4])) {
        $width = $link[4];
    }

    if (!is_null($link[5])) {
        $height = $link[5];
    }

    if (!is_null($link[6])) {
        $thumbnailformat = $link[6];
    }

    $csswidth = $width . "px";
    $cssheight = $height . "px";

    $flowplayerswfpath = $CFG->wwwroot.'/filter/flowplayer/flowplayer-3.2.7.swf';
    $flowplayerjspath = $CFG->wwwroot.'/filter/flowplayer/flowplayer-3.2.6.min.js';
    $jquerypath = $CFG->wwwroot.'/filter/flowplayer/jquery.min.js';
    $audioiconpath = $CFG->wwwroot.'/filter/flowplayer/audio-icon.png';

    $output = <<<OET
    <script type="text/javascript" src="$flowplayerjspath"></script>
    <script type="text/javascript" src="$jquerypath"></script>
    <style type="text/css">
    audio#audio_$id, #player_$id {
            display:block;
            width:$csswidth;
            height:$cssheight;
    }
    </style>

    <a id="player_$id" href="$url"></a>

    <script language="JavaScript">
    flowplayer("player_$id", "$flowplayerswfpath", {
        plugins: {
            controls: { autoHide: 'never' }
        },

        clip: {
                autoPlay: false,
                autoBuffering: true,
                url: '$url',
                coverImage: { url: '$audioiconpath', scaling: 'orig' }
            }
        }
    );
    </script>

    <audio id="audio_$id" controls>
        <source src="$url" type="audio/mp3">
    </audio>

    <script type="text/javascript">
        $(document).ready(function() {
            var hasFlash = false;
            try {
                var fo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
                if(fo) hasFlash = true;
            }catch(e){
                if(navigator.mimeTypes ["application/x-shockwave-flash"] != undefined) hasFlash = true;
            }

            //if browser has flash, remove the audio tag
            if(hasFlash){
                $('#audio_$id').remove();
            } else {
                // otherwise remove the player tag
                $('#player_$id').remove();
            }
        }); //end doc ready

</script>
OET;

    return $output;
}