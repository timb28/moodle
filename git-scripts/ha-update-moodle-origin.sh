##
## Shell script to update Moodle source via Git
##

echo "Updating Moodle from Origin"
(cd ../ && git fetch origin)
(cd ../ && git checkout HA-Moodle30)
(cd ../ && git pull)