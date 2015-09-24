#!/bin/bash
## Used by:
## - All Moodle servers
##
echo "Cloning Styles Atto plugin from Origin"
(cd .. && git clone https://github.com/moodleuulm/moodle-atto_styles.git lib/editor/atto/plugins/styles)
(cd .. && echo /lib/editor/atto/plugins/styles/ >> .git/info/exclude)
(cd .. && git --git-dir=lib/editor/atto/plugins/styles/.git remote add upstream https://github.com/moodleuulm/moodle-atto_styles.git)