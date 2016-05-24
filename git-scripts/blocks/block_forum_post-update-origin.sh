#!/bin/bash
echo "Updating Forum Post Block"
(cd ../blocks/forum_post/ && git fetch origin && git checkout HA-Moodle30 && git pull)