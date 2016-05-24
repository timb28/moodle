#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Updating LenAuth authentication module from Origin"
(cd ../auth/lenauth/ && git fetch origin && git checkout HA-Moodle30 && git pull)
