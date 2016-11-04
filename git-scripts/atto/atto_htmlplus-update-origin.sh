#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Updating HTML+ Atto plugin from Origin"
(cd ../lib/editor/atto/plugins/htmlplus/ && git fetch origin && git checkout master && git pull)