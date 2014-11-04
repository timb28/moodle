#!/bin/bash
echo "Updating Questionnaire Module from Origin"
(cd ../../mod/questionnaire/ && git fetch upstream)
(cd ../../mod/questionnaire/ && git checkout upstream/HA-Moodle27)