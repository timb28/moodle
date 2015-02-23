#!/bin/bash
echo "Updating Apply Enrolment Plugin from Origin"
(cd ../enrol/apply/ && git fetch origin)
(cd ../enrol/apply/ && git pull origin MOODLE_25_STABLE)