#!/bin/bash
echo "Updating Questionnaire Module from Origin"
(cd ../mod/questionnaire/ && git fetch origin && git checkout HA-Moodle30 && git pull)