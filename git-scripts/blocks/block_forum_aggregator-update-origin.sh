echo "Updating Forum Aggregator Block from Origin"
(cd ../blocks/forum_aggregator/ && git fetch origin && git checkout HA-Moodle30 && git pull)