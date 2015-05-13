#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Text Quiz Question Type
echo "Updating Drag and Drop Text Question Type from Upstream"
(cd ../question/type/ddwtos && git fetch upstream)
(cd ../question/type/ddwtos && git checkout -b HA-Moodle28)
(cd ../question/type/ddwtos && git merge upstream/master)