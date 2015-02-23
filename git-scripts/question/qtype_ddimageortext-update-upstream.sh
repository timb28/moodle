#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Images Quiz Question Type
echo "Updating Drag and Drop Image or Text Question Type from Upstream"
(cd ../question/type/ddimageortext && git fetch upstream && git pull upstream master)