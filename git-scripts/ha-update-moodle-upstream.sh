##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle from Upstream"
(cd ../ && git fetch upstream)
(cd ../ && git checkout upstream/MOODLE_27_STABLE)
(cd ../ && git branch HA-Moodle27)
(cd ../ && git checkout HA-Moodle27)
(cd ../ && git merge MOODLE_25_STABLE --strategy-option ours)
## Then manually resolve the merge conflicts.