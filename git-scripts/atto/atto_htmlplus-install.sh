#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Cloning HTML+ Atto plugin from Origin"
(cd .. && git clone https://github.com/andrewnicols/moodle-atto_htmlplus.git lib/editor/atto/plugins/htmlplus)
(cd .. && echo /lib/editor/atto/plugins/htmlplus/ >> .git/info/exclude)
(cd .. && git --git-dir=lib/editor/atto/plugins/htmlplus/.git remote add upstream https://github.com/andrewnicols/moodle-atto_htmlplus.git)