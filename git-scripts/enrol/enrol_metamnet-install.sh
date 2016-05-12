echo "Cloning Meta MNet Remote Enrolment Plugin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-enrol_metamnet.git enrol/mnet_remote)
(cd .. && echo /enrol/metamnet/ >> .git/info/exclude)
(cd ../enrol/metamnet && git branch --track master origin/master)
(cd ../enrol/metamnet && git checkout master)