#!/bin/bash
echo "Updating Questionnaire Module from Upstream"
(cd ../mod/questionnaire/ && git fetch upstream)
(cd ../mod/questionnaire/ && git checkout upstream/MOODLE_27_STABLE)
(cd ../mod/questionnaire/ && git merge HA-Moodle27)