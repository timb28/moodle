echo "Cloning XP Block"
(cd .. && git clone https://github.com/FMCorz/moodle-block_xp blocks/xp)
(cd .. && git --git-dir=blocks/xp/.git remote add upstream https://github.com/FMCorz/moodle-block_xp)
(cd .. && echo /blocks/xp/ >> .git/info/exclude)
## (cd ../blocks/quickmail && git branch --track HA-Moodle30 origin/HA-Moodle30)
## (cd ../blocks/quickmail && git checkout HA-Moodle30)