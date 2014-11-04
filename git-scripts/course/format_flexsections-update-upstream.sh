echo "Updating Flexible sections course format from Upstream"
(cd ../course/format/flexsections/ && git fetch upstream)
(cd ../course/format/flexsections/ && checkout upstream/master)
(cd ../course/format/flexsections/ && git merge HA-Moodle27)