#!/bin/bash
##
## Shell script to download all the third party Moodle plugins used by Harcourts Academy
##

## Certificates
./mod_certificate-install.sh
 
## Configurable Reports
./block_configurable_report-install.sh
 
## Questionnaire
./mod_questionnaire-install.sh

## Quiz Question Types

## OU Drag and Drop Images Quiz Question Type
echo "Cloning Drag and Drop Image or Text Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_ddimageortext.git question/type/ddimageortext)
(cd .. && echo /question/type/ddimageortext/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddimageortext/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddimageortext.git)
 
## OU Drag and Drop Text Quiz Question Type
echo "Cloning Drag and Drop Text Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_ddwtos.git question/type/ddwtos)
(cd .. && echo /question/type/ddwtos/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddwtos/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddwtos.git)
 
## OU Select Missing Words Quiz Question Type
echo "Cloning Gap Select Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_gapselect.git question/type/gapselect)
(cd .. && echo /question/type/gapselect/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/gapselect/.git remote add upstream https://github.com/moodleou/moodle-qtype_gapselect.git)
 
## OU Multiple Response Quiz Question Type
echo "Updating Multiple Choice, Multiple Response Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_oumultiresponse.git question/type/oumultiresponse)
(cd .. && echo /question/type/oumultiresponse/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/oumultiresponse/.git remote add upstream https://github.com/moodleou/moodle-qtype_oumultiresponse.git )
