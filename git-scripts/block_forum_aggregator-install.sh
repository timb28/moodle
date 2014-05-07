echo "Cloning Forum Aggregator Block"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-block_forum_aggregator.git blocks/forum_aggregator)
(cd .. && git --git-dir=blocks/forum_aggregator/.git remote add upstream https://github.com/t6nis/moodle-block_forum_aggregator.git)
(cd .. && echo /blocks/forum_aggregator/ >> .git/info/exclude)
(cd ../blocks/forum_aggregator && git branch -a)
(cd ../blocks/forum_aggregator && git branch --track 2013091300 origin/2013091300)
(cd ../blocks/forum_aggregator && git checkout 2013091300)