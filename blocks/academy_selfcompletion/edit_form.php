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
 * Form for editing HTML block instances.
 *
 * @package   block_academy_selfcompletion
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing HTML block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_academy_selfcompletion_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        // Fields for editing the blocks message.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('textarea', 'config_message', get_string('configmessage', 'block_academy_selfcompletion'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('config_message', PARAM_RAW);
    }

    function set_data($defaults) {
        if (!$this->block->user_can_edit() && !empty($this->block->config->message)) {
            // If a message has been set but the user cannot edit it format it nicely
            $message = $this->block->config->message;
            $defaults->config_message = format_string($message, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->message);
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        if (isset($message)) {
            // Reset the preserved title
            $this->block->config->message = $message;
        }
    }
}
