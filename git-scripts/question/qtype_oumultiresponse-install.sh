#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Multiple Response Quiz Question Type
echo "Updating Multiple Choice, Multiple Response Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_oumultiresponse.git question/type/oumultiresponse)
(cd .. && echo /question/type/oumultiresponse/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/oumultiresponse/.git remote add upstream https://github.com/moodleou/moodle-qtype_oumultiresponse.git )
(cd ../question/type/oumultiresponse && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../question/type/oumultiresponse && git checkout HA-Moodle28)