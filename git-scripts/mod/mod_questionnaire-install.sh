#!/bin/bash
echo "Cloning Questionnaire Module"
(cd ../.. && git clone https://github.com/HarcourtsAcademy/moodle-mod_questionnaire.git mod/questionnaire)
(cd ../.. && echo /mod/questionnaire/ >> .git/info/exclude)
(cd ../../mod/questionnaire && git remote add upstream https://github.com/remotelearner/moodle-mod_questionnaire.git)
(cd ../../mod/questionnaire && git branch -a)
(cd ../../mod/questionnaire && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../../mod/questionnaire && git checkout HA-Moodle27)