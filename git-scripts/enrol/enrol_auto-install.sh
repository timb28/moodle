echo "Cloning Auto Enrolment Plugin"
(cd .. && git clone https://github.com/eugeneventer/moodle-enrol_auto.git enrol/auto)
(cd .. && echo /enrol/auto/ >> .git/info/exclude)
(cd ../enrol/auto && git branch --track MOODLE_29_STABLE origin/MOODLE_29_STABLE)
(cd ../enrol/auto && git checkout MOODLE_29_STABLE)