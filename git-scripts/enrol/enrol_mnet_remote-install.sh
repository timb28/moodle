echo "Cloning Remote MNet Enrolment Plugin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-enrol_mnet_remote.git enrol/mnet_remote)
(cd .. && echo /enrol/mnet_remote/ >> .git/info/exclude)
(cd ../enrol/mnet_remote && git branch --track master origin/master)
(cd ../enrol/mnet_remote && git checkout master)