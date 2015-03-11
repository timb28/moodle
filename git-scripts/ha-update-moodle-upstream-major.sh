##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle from Upstream. Ensure Moodle branches in this script are correct before continuing."
read -p "Are you sure? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    (cd ../ && git fetch upstream)
    (cd ../ && git checkout upstream/MOODLE_27_STABLE)
    (cd ../ && git branch HA-Moodle27)
    (cd ../ && git checkout HA-Moodle27)
    (cd ../ && git merge MOODLE_25_STABLE --strategy-option ours)
    ## Then manually resolve the merge conflicts.
fi