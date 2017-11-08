<?php

/**
 * @package block_academy_library
 * @copyright 2011 onwards Harcourts International
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup task for the Academy Library block based on the html block
 * (requires encode_content_links in some configdata attrs)
 *
 */
class backup_academy_library_block_task extends backup_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array('content');
    }

    public function get_configdata_encoded_attributes() {
        return array('text'); // We need to encode some attrs in configdata
    }

    static public function encode_content_links($content) {
        return $content; // No special encoding of links
    }
}

