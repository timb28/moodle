#!/bin/bash
echo "Updating Apply Enrolment Plugin from Origin"
(cd ../enrol/apply/ && git fetch origin && git checkout HA-Moodle30 && git pull)