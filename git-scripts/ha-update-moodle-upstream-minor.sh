##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle from Upstream"
(cd ../ && git fetch upstream)
(cd ../ && git checkout upstream/MOODLE_28_STABLE)
(cd ../ && git merge HA-Moodle28)
## Then manually resolve the merge conflicts.