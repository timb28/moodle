echo "Cloning Apply Enrolment Plugin"
(cd .. && git clone https://github.com/HarcourtsAcademy/apply.git enrol/apply)
(cd .. && git --git-dir=enrol/apply/.git remote add upstream https://github.com/emeneo/apply.git)
(cd .. && echo /enrol/apply/ >> .git/info/exclude)
(cd ../enrol/apply && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../enrol/apply && git checkout HA-Moodle28)