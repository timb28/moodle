#!/bin/bash
## Used by:
## - NZ Online CE and other courses
##
echo "Updating Configurable Reports Block from Origin"
(cd ../blocks/configurable_reports && git fetch origin && git checkout HA-Moodle30 && git pull)
