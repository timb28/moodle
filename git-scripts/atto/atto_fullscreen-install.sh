#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Cloning Fullscreen Atto plugin from Origin"
(cd .. && git clone https://github.com/dthies/moodle-atto_fullscreen.git lib/editor/atto/plugins/fullscreen)
(cd .. && echo /lib/editor/atto/plugins/fullscreen/ >> .git/info/exclude)
(cd .. && git --git-dir=lib/editor/atto/plugins/fullscreen/.git remote add upstream https://github.com/dthies/moodle-atto_fullscreen.git)