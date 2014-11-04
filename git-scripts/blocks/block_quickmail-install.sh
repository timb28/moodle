echo "Cloning Quickmail Block"
(cd ../.. && git clone https://github.com/HarcourtsAcademy/quickmail.git blocks/quickmail)
(cd ../.. && git --git-dir=blocks/quickmail/.git remote add upstream https://github.com/lsuits/quickmail.git)
(cd ../.. && echo /blocks/quickmail/ >> .git/info/exclude)
(cd ../../blocks/quickmail && git branch -a)
(cd ../../blocks/quickmail && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../../blocks/quickmail && git checkout HA-Moodle27)