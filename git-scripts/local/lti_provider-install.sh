echo "Cloning LTI Provider Plugin"
(cd .. && git clone https://github.com/jleyva/moodle-local_ltiprovider.git local/ltiprovider)
(cd .. && git --git-dir=local/ltiprovider/.git remote add upstream https://github.com/jleyva/moodle-local_ltiprovider.git)
(cd .. && echo /local/ltiprovider/ >> .git/info/exclude)
(cd ../local/ltiprovider && git branch --track MOODLE_27_STABLE origin/MOODLE_27_STABLE)
(cd ../local/ltiprovider && git checkout MOODLE_27_STABLE)