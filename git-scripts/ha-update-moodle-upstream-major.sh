##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle to version 3.0 from Upstream. Ensure Moodle branches in this script are correct before continuing."
read -p "Are you sure? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    (cd ../ && git fetch upstream)
    (cd ../ && git checkout upstream/MOODLE_30_STABLE)
#    (cd ../ && git branch HA-Moodle30)
    (cd ../ && git checkout HA-Moodle30)
    (cd ../ && git merge origin/HA-Moodle286 --strategy-option ours)
    ## Then manually resolve the merge conflicts.
fi
