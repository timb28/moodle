<?php

/**
 * A custom renderer for the Academy theme to produce customised content.
 *
 * @package    theme
 * @subpackage academy_clean
 * @copyright  Harcourts International Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/theme/bootstrapbase/renderers.php');

class theme_academy_clean_core_renderer extends theme_bootstrapbase_core_renderer {

   /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        $breadcrumbs[] = '<a href="/">Home</a>'; // Make the first breadcrumb a link to our main website.
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $list_items = '<li>'.join(" $divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$list_items</ul>";
    } 
}

?>
