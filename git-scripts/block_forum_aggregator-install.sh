## Used by:
## - iStart24 Online
##
echo "Cloning Forum Aggregator Block"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-block_forum_aggregator.git blocks/forum_aggregator)
(cd .. && git --git-dir=blocks/forum_aggregator/.git remote add upstream https://github.com/t6nis/moodle-block_forum_aggregator.git)
(cd .. && echo /blocks/forum_aggregator/ >> .git/info/exclude)
(cd ../blocks/forum_aggregator && git branch -a)
(cd ../blocks/forum_aggregator && git branch --track HA-BlockForumAggregator origin/HA-BlockForumAggregator)
(cd ../blocks/forum_aggregator && git checkout HA-BlockForumAggregator)