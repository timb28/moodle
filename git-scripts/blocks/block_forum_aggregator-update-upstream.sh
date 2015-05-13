echo "Updating Forum Aggregator Block from Upstream"
(cd ../blocks/forum_aggregator/ && git fetch upstream
(cd ../blocks/forum_aggregator/ && git checkout -b HA-Moodle28
(cd ../blocks/forum_aggregator/ && git merge upstream/master)