#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Select Missing Words Quiz Question Type
echo "Cloning Gap Select Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_gapselect.git question/type/gapselect)
(cd .. && echo /question/type/gapselect/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/gapselect/.git remote add upstream https://github.com/moodleou/moodle-qtype_gapselect.git)
(cd ../question/type/gapselect && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../question/type/gapselect && git checkout HA-Moodle28)