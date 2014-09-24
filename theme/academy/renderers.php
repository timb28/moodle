<?php

/**
 * A custom renderer for the Academy theme to produce customised content.
 *
 * @package    theme
 * @subpackage academy
 * @copyright  Harcourts International Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_academy_core_renderer extends core_renderer {

    /**
     * Produces the navigation bar for the academy theme
     *
     * @return string
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();

        $htmlblocks = array();
        
        // Add a home link to our Main Home Page
        $academyhomelink = array('href' => '/');
        $academyhometag = html_writer::tag('a', 'Home', $academyhomelink);
        $content = html_writer::tag('li', $academyhometag);
        $htmlblocks[] = $content;
                
        // Iterate the navarray and display each node
        $itemcount = count($items);
        $separator = get_separator();
        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            $item->hideicon = true;

            $content = html_writer::tag('li', $separator.$this->render($item));
            $htmlblocks[] = $content;
        }

        //accessibility: heading for navbar list  (MDL-20446)
        $navbarcontent = html_writer::tag('span', get_string('pagepath'), array('class'=>'accesshide'));
        $navbarcontent .= html_writer::tag('ul', join('', $htmlblocks));
        // XHTML
        return $navbarcontent;
    }
}

?>
