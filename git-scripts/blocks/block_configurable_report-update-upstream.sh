## Used by:
## - NZ Online CE and other courses
##
echo "Updating Configurable Reports Block from Upstream"
(cd ../../blocks/configurable_reports && git fetch upstream)
(cd ../../blocks/configurable_reports && git checkout upstream CR_23_STABLE)
(cd ../../blocks/configurable_reports && git merge HA-Moodle27)
