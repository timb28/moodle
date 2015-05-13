#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Select Missing Words Quiz Question Type
echo "Updating Gap Select Question Type from Upstream"
(cd ../question/type/gapselect && git fetch upstream)
(cd ../question/type/gapselect && git checkout -b HA-Moodle28)
(cd ../question/type/gapselect && git merge upstream/master)