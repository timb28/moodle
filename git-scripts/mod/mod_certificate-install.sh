#!/bin/bash
echo "Cloning Certificates Module from Origin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-mod_certificate.git mod/certificate)
(cd .. && git --git-dir=mod/certificate/.git remote add upstream https://github.com/markn86/moodle-mod_certificate.git)
(cd .. && echo /mod/certificate/ >> .git/info/exclude)
(cd ../mod/certificate && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../mod/certificate && git checkout HA-Moodle27)