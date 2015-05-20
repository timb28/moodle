## Video File
echo "Cloning Video File Module"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-mod_videofile.git mod/videofile)
(cd .. && git --git-dir=mod/videofile/.git remote add upstream https://github.com/lemonad/moodle-mod_videofile.git)
(cd .. && echo /mod/videofile/ >> .git/info/exclude)
(cd ../mod/videofile && git branch --track HA-Moodle28 origin/HA-Moodle28)
(cd ../mod/videofile && git checkout HA-Moodle28 )