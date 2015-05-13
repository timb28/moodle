#!/bin/bash
## Used by:
## - AU Sales Registration
##
echo "Cloning Pattern Patching Question Type from Upstream"
(cd ../question/type/pmatch && git fetch upstream)
(cd ../question/type/pmatch && git checkout -b HA-Moodle28)
(cd ../question/type/pmatch && git merge upstream/master)