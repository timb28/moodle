echo "Updating Flexible sections course format from Upstream"
(cd ../course/format/flexsections/ && git fetch upstream)
(cd ../course/format/flexsections/ && git checkout -b HA-Moodle28)
(cd ../course/format/flexsections/ && git merge upstream/master)