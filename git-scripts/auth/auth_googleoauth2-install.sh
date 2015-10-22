#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Cloning OAuth2 authentication module from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-auth_googleoauth2.git auth/googleoauth2)
(cd .. && echo /auth/googleoauth2/ >> .git/info/exclude)
(cd .. && git --git-dir=auth/googleoauth2/.git remote add upstream https://github.com/mouneyrac/moodle-auth_googleoauth2.git)
(cd ../auth/googleoauth2 && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../auth/googleoauth2 && git checkout HA-Moodle28)