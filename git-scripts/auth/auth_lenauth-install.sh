#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Cloning LenAuth authentication module from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-auth_lenauth.git auth/lenauth)
(cd .. && echo /auth/lenauth/ >> .git/info/exclude)
(cd .. && git --git-dir=auth/lenauth/.git remote add upstream https://github.com/tigusigalpa/moodle-auth_lenauth)
(cd ../auth/lenauth && git branch --track HA-Moodle30 origin/HA-Moodle30)
(cd ../auth/lenauth && git checkout HA-Moodle30)