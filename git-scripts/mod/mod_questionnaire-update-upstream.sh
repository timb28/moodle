#!/bin/bash
echo "Updating Questionnaire Module from Upstream"
(cd ../mod/questionnaire/ && git fetch upstream)
(cd ../mod/questionnaire/ && git merge upstream/MOODLE_27_STABLE)