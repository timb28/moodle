echo "Cloning Apply Enrolment Plugin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-enrol_apply.git enrol/apply)
(cd .. && git --git-dir=enrol/apply/.git remote add upstream https://github.com/lemonad/moodle-mod_videofile.git)
(cd .. && echo /enrol/apply/ >> .git/info/exclude)
(cd ../enrol/apply && git branch --track MOODLE_25_STABLE origin/MOODLE_25_STABLE)
(cd ../enrol/apply && git checkout MOODLE_25_STABLE)