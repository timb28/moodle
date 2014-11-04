## Used by:
## - iStart24 Online
##
echo "Cloning Forum Post Block"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-block_forum_post.git blocks/forum_post)
(cd .. && echo /blocks/forum_post/ >> .git/info/exclude)
(cd ../blocks/forum_post && git branch --track HA-Moodle27 origin/HA-Moodle27)
(cd ../blocks/forum_post && git checkout HA-Moodle27)