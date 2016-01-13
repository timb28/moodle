<?php

class block_academy_library_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        // Section header according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Select country visitors block will appear to (always appears to editors)
        $mform->addElement('select', 'config_country', get_string('blockcountry', 'block_academy_library'), $this->block->academyLibraryCountries);

        // HTML editor
        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
        $mform->addElement('editor', 'config_text', get_string('blockstring', 'block_academy_library'), null, $editoroptions);
        $mform->setType('config_text', PARAM_RAW); // XSS is prevented when printing the block contents and serving files

    }

    function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftid_editor = file_get_submitted_draft_itemid('config_text');
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $defaults->config_text['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_academy_library', 'content', 0, array('subdirs'=>true), $currenttext);
            $defaults->config_text['itemid'] = $draftid_editor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
        }

        // have to delete text here, otherwise parent::set_data will empty content
        // of editor
        unset($this->block->config->text);
        parent::set_data($defaults);
        // restore $text
        $this->block->config->text = $text;
    }
}

?>
