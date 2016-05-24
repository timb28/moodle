#!/bin/bash
echo "Updating Subcourse Module from Origin"
(cd ../mod/subcourse/ && git fetch origin && git checkout HA-Moodle30 && git pull)