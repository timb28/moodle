#!/bin/bash
echo "Updating Questionnaire Module from Upstream"
(cd ../mod/questionnaire/ && git fetch upstream)
(cd ../mod/questionnaire/ && git checkout upstream/MOODLE_28_STABLE)
(cd ../mod/questionnaire/ && git checkout -b HA-Moodle28)
(cd ../mod/questionnaire/ && git merge origin/HA-Moodle27)