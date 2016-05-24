#!/bin/bash
echo "Updating VideoFile Module from Origin"
(cd ../mod/videofile/ && git fetch origin && git checkout HA-Moodle30 && git pull)