#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Images Quiz Question Type
echo "Updating Drag and Drop Image or Text Question Type from Origin"
(cd ../question/type/ddimageortext && git fetch origin && git pull origin master)