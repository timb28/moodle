<?php
/**
 * Block: Harcourts Answers
 *
 * Displays country specific links to Harcourts Answers.
 *
 * @package     block_harcourts_answers
 * @author      Tim Butler
 * @copyright   2011 onwards Harcourts Academy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

class block_harcourts_answers extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_harcourts_answers');
    }

    public function get_content() {
        $this->config->format = FORMAT_HTML;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';

        return $this->content;
    }

    public function specialization() {

        $this->title = get_string('blocktitle', 'block_harcourts_answers');

        if (empty($this->content->text)) {
            global $CFG;
            require_once($CFG->libdir . '/harcourtslib.php');

            $answerslink = '<a href="' . get_harcourtsanswerslink() . '">' . get_string('linkname', 'block_harcourts_answers') . '</a>';
            $this->content->text = get_string('blockstring', 'block_harcourts_answers', $answerslink);
        }
    }

    /**
     * Returns true or false, depending on whether this block has any content to display.
     *
     * @return boolean
     */
    public function is_empty() {
        return get_harcourtsanswerslink() == null;
    }

}

?>
