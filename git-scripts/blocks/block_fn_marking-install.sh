echo "Cloning NED Marking Block"
(cd .. && git clone https://github.com/fernandooliveira/moodle-block_marking_manager.git blocks/fn_marking)
(cd .. && git --git-dir=blocks/fn_marking/.git remote add upstream https://github.com/fernandooliveira/moodle-block_marking_manager.git)
(cd .. && echo /blocks/fn_marking/ >> .git/info/exclude)
## (cd ../blocks/aaa && git branch --track HA-Moodle30 origin/HA-Moodle30)
## (cd ../blocks/aaa && git checkout HA-Moodle30)
