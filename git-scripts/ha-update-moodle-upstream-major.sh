##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle to version 2.8 from Upstream. Ensure Moodle branches in this script are correct before continuing."
read -p "Are you sure? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    (cd ../ && git fetch upstream)
    (cd ../ && git checkout upstream/MOODLE_28_STABLE)
#    (cd ../ && git branch HA-Moodle28)
    (cd ../ && git checkout HA-Moodle28)
    (cd ../ && git merge origin/HA-Moodle27 --strategy-option ours)
    ## Then manually resolve the merge conflicts.
fi
