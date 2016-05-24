#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Updating Styles Atto plugin from Origin"
(cd ../lib/editor/atto/plugins/styles/ && git fetch origin && git checkout master && git pull)