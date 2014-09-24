<?php
/**
 * Block: Harcourts Resource Library
 *
 * Displays country specific links to documents and folders in the H1 Resource Library.
 *
 * @package     block_academy_library
 * @author      Tim Butler
 * @copyright   2011 onwards Harcourts Academy
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

class block_academy_library extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_academy_library');
        $this->academyLibraryCountries = array(
            "au" => get_string('countryau', 'block_academy_library'),
            "nz" => get_string('countrynz', 'block_academy_library'),
            "za" => get_string('countryza', 'block_academy_library'),
            "us" => get_string('countryus', 'block_academy_library')
            );
    }

    public function applicable_formats() {
        return array('all' => true, 'my' => false);
    }


    public function get_content() {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        // Display correct library for visitor's country or all libraries if an editor
        $visitorcountry = @$_COOKIE['usercountry'];

        if (!$this->user_can_edit() && $visitorcountry != $this->config->country) {
            $this->content = null;
            return $this->content;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;

        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // rewrite url
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_academy_library', 'content', NULL);
            // Default to FORMAT_HTML which is what will have been used before the
            // editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = format_text($this->config->text, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        unset($filteropt); // memory footprint

        return $this->content;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_allow_multiple() {
      return true;
    }

    /**
     * Returns true or false, depending on whether this block has any content to display
     * and whether the user has permission and is in the right country to view the block
     *
     * @return boolean
     */
    public function is_empty() {
        // Check visitor's country
        $visitorcountry = @$_COOKIE['usercountry'];

        if ( !has_capability('moodle/block:view', $this->context) ||
                (!$this->user_can_edit() && $visitorcountry != $this->config->country) ) {
            return true;
        }

        $this->get_content();
        return(empty($this->content->text) && empty($this->content->footer));
    }

    public function specialization() {
        $this->title = get_string('blocktitledefault', 'block_academy_library');

        // Append the country name if user can see all countries library blocks
        if ($this->user_can_edit() && !empty($this->config->country)) {
            $this->title = $this->title . " - " . get_string('country'.$this->config->country, 'block_academy_library');
        }

        if (empty($this->config->text)) {
            $this->config->text = get_string('blockstringdefault', 'block_academy_library');
        }
    }

    /**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_academy_library', 'content', 0, array('subdirs'=>true), $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_academy_library');
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = get_context_instance_by_id($this->instance->parentcontextid)) {
            return false;
        }
        //find out if this block is on the profile page
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // this is exception - page is completely private, nobody else may see content there
                // that is why we allow JS here
                return true;
            } else {
                // no JS on public personal pages, it would be a big security issue
                return false;
            }
        }

        return true;
    }

}

?>
