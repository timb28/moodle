echo "Cloning XP Availability module"
(cd .. && git clone https://github.com/FMCorz/moodle-availability_xp availability/condition/xp)
(cd .. && git --git-dir=availability/condition/xp/.git remote add upstream https://github.com/FMCorz/moodle-availability_xp)
(cd .. && echo /availability/condition/xp/ >> .git/info/exclude)
## (cd ../blocks/quickmail && git branch --track HA-Moodle30 origin/HA-Moodle30)
## (cd ../blocks/quickmail && git checkout HA-Moodle30)