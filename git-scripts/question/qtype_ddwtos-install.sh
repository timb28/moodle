#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Text Quiz Question Type
echo "Cloning Drag and Drop Text Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_ddwtos.git question/type/ddwtos)
(cd .. && echo /question/type/ddwtos/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddwtos/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddwtos.git)
(cd ../question/type/ddwtos && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../question/type/ddwtos && git checkout HA-Moodle28)