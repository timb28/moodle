#!/bin/bash
echo "Updating Unanswered Discussions Block"
(cd ../blocks/unanswered_discussions/ && git fetch origin && git checkout HA-Moodle30 && git pull)