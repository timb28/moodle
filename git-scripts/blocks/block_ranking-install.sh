echo "Cloning Ranking Block"
(cd .. && git clone https://github.com/willianmano/moodle-block_ranking.git blocks/ranking)
(cd .. && git --git-dir=blocks/ranking/.git remote add upstream https://github.com/willianmano/moodle-block_ranking.git)
(cd .. && echo /blocks/ranking/ >> .git/info/exclude)
## (cd ../blocks/quickmail && git branch --track HA-Moodle30 origin/HA-Moodle30)
## (cd ../blocks/quickmail && git checkout HA-Moodle30)