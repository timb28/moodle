## Used by:
## - NZ Online CE and other courses
##
echo "Updating Configurable Reports Block from Origin"
(cd ../blocks/configurable_reports && git fetch origin)
(cd ../blocks/configurable_reports && git pull origin CR_23_STABLE)
(cd ../blocks/configurable_reports && git checkout CR_23_STABLE)
