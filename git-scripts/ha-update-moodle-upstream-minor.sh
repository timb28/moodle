##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle from Upstream"
(cd ../ && git fetch upstream)
(cd ../ && git checkout upstream/MOODLE_30_STABLE)
(cd ../ && git merge HA-Moodle30 --strategy-option ours)
## Then manually resolve the merge conflicts.