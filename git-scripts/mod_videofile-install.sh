## Video File
echo "Cloning Video File Module"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-mod_videofile.git mod/videofile)
(cd .. && git --git-dir=mod/videofile/.git remote add upstream https://github.com/lemonad/moodle-mod_videofile.git)
(cd .. && echo /mod/videofile/ >> .git/info/exclude)
(cd ../mod/videofile && git branch -a)
(cd ../mod/videofile && git branch --track HA-VideoFile origin/HA-VideoFile)
(cd ../mod/videofile && git checkout HA-VideoFile)