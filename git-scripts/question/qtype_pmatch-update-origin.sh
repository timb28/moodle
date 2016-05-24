#!/bin/bash
## Used by:
## - AU Sales Registration
##
echo "Cloning Pattern Patching Question Type from Origin"
(cd ../question/type/pmatch && git fetch origin && git checkout HA-Moodle30 && git pull)