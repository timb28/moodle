#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Updating LenAUth authentication module from Origin"
(cd ../auth/lenauth && git fetch origin && git checkout HA-Moodle28)
