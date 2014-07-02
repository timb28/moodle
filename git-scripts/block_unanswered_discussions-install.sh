echo "Cloning Unanswered Discussions Block"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-block_unanswered_discussions.git blocks/unanswered_discussions)
(cd .. && git --git-dir=blocks/unanswered_discussions/.git remote add upstream https://github.com/deraadt/moodle-block_unanswered_discussions.git)
(cd .. && echo /blocks/unanswered_discussions/ >> .git/info/exclude)
## (cd ../blocks/unanswered_discussions && git branch -a)
## (cd ../blocks/unanswered_discussions && git branch --track master origin/master)
## (cd ../blocks/unanswered_discussions && git checkout master)