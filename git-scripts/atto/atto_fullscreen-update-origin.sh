#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Updating Fullscreen Atto plugin from Origin"
(cd ../lib/editor/atto/plugins/fullscreen/ && git fetch origin && git checkout master && git pull)