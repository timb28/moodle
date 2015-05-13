#!/bin/bash
## Used by:
## - AU Sales Registration
##
echo "Cloning Pattern Patching Question Type from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_pmatch.git question/type/pmatch)
(cd .. && echo /question/type/pmatch/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/pmatch/.git remote add upstream https://github.com/moodleou/moodle-qtype_pmatch.git)
(cd ../question/type/pmatch && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../question/type/pmatch && git checkout HA-Moodle28)