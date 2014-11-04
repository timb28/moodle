## Game
echo "Cloning Game Module"
(cd ../.. && git clone https://github.com/HarcourtsAcademy/moodle-mod_game.git mod/game)
(cd ../.. && echo /mod/game/ >> .git/info/exclude)
(cd ../../mod/game && git remote add upstream https://github.com/bdaloukas/moodle-mod_game.git)
(cd ../../mod/game && git branch -a)
(cd ../../mod/game && git branch --track MOODLE_20_STABLE origin/MOODLE_20_STABLE)
(cd ../../mod/game && git checkout MOODLE_20_STABLE)