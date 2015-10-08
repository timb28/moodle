#!/bin/bash
## Used by:
## - iStart Online v2
##
echo "Cloning Progress Bar Block from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/Moodle-block_progress.git blocks/progress)
(cd .. && echo /blocks/progress/ >> .git/info/exclude)
(cd .. && git --git-dir=blocks/progress/.git remote add upstream https://github.com/deraadt/Moodle-block_progress.git)
