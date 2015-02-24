echo "Updating Flexible sections course format from Upstream"
(cd ../course/format/flexsections/ && git fetch upstream)
(cd ../course/format/flexsections/ && git merge upstream/MOODLE_27_STABLE)