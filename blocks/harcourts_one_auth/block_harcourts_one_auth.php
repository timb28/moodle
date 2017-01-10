<?php
/**
 * Block: Harcourts One Authentication
 *
 * Students must login to Harcourts One to view the page
 * on which this block appears. Can be added to a course or course category.
 *
 * @package     block_harcourts_one_auth
 * @author      Tim Butler
 * @copyright   2013 onwards Harcourts Academy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();
require_once("{$CFG->libdir}/harcourtslib.php");

class block_harcourts_one_auth extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_harcourts_one_auth');
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false);
    }

    public function get_content() {
      
        if ($this->content !== null) {
            return $this->content;
        }

        $this->title = get_string('blocktitle', 'block_harcourts_one_auth');

        $contents = '';
        if (empty($this->content->text)) {
            $contents = get_string('blocktext', 'block_harcourts_one_auth');
        }
        
        $this->content = new stdClass;
        $this->content->text = $contents;
        $this->content->footer = '';

        return $this->content;
    }

    public function specialization() {
        // Harcourts One login required if the user is a not site admin
        if (!is_siteadmin()) {
            global $PAGE;
            academy_h1_authorisation(rawurlencode($PAGE->url));
        }
    }

    /**
     * Returns true or false, depending on whether this block has any content to display.
     *
     * @return boolean
     */
    public function is_empty() {
        return false;
    }
    
    /**
     * Hides the entire block from everyone who is not a site administrator.
     *
     * @return array
     */
    public function html_attributes() {
        if (!is_siteadmin()) {
            $attributes = parent::html_attributes(); // Get default values
            $attributes['class'] .= ' hide'; // Hide the block
            return $attributes;
        }
    }

}

?>
