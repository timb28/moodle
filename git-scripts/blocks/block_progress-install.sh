#!/bin/bash
## Used by:
## ---=== TESTING ===---
##
echo "Cloning Progress Bar Block from Upstream"
(cd .. && git clone https://github.com/deraadt/Moodle-block_progress.git blocks/progress)
(cd .. && echo /blocks/progress/ >> .git/info/exclude)
(cd .. && git --git-dir=blocks/progress/.git remote add upstream https://github.com/deraadt/Moodle-block_progress.git)
