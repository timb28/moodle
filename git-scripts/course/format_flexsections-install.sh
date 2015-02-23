echo "Cloning Flexible sections course format"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-format_flexsections.git course/format/flexsections)
(cd .. && git --git-dir=course/format/flexsections/.git remote add upstream https://github.com/marinaglancy/moodle-format_flexsections.git)
(cd .. && echo /course/format/flexsections/ >> .git/info/exclude)
(cd ../course/format/flexsections && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../course/format/flexsections && git checkout HA-Moodle27)