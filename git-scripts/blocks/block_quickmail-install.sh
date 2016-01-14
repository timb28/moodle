echo "Cloning Quickmail Block"
(cd .. && git clone https://github.com/HarcourtsAcademy/quickmail.git blocks/quickmail)
(cd .. && git --git-dir=blocks/quickmail/.git remote add upstream https://github.com/lsuits/quickmail.git)
(cd .. && echo /blocks/quickmail/ >> .git/info/exclude)
(cd ../blocks/quickmail && git branch --track HA-Moodle30 origin/HA-Moodle30)
(cd ../blocks/quickmail && git checkout HA-Moodle30)