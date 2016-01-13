#!/bin/bash
## Used by:
## - AU Sales Registration
##
echo "Cloning Pattern Patching Question Type from Origin"
(cd ../question/type/pmatch && git fetch origin)
(cd ../question/type/pmatch && git checkout HA-Moodle28)