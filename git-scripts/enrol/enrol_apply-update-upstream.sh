#!/bin/bash
echo "Updating Apply Enrolment Plugin from Upstream"
(cd ../enrol/apply/ && git fetch upstream)
(cd ../enrol/apply/ && git pull upstream master)