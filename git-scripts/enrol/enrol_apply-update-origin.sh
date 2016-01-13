#!/bin/bash
echo "Updating Apply Enrolment Plugin from Origin"
(cd ../enrol/apply/ && git fetch origin)
(cd ../enrol/apply/ && git checkout HA-Moodle28)