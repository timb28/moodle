#!/bin/bash
## Used by:
## - NZ Online CE and other courses
##
echo "Cloning Configurable Reports Block from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-block_configurablereports.git blocks/configurable_reports)
(cd .. && echo /blocks/configurable_reports/ >> .git/info/exclude)
(cd .. && git --git-dir=blocks/configurable_reports/.git remote add upstream https://github.com/jleyva/moodle-block_configurablereports.git)
(cd ../blocks/configurable_reports && git branch -a)
(cd ../blocks/configurable_reports && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../blocks/configurable_reports && git checkout HA-Moodle27)