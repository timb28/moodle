#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Text Quiz Question Type
echo "Updating Drag and Drop Text Question Type from Origin"
(cd ../question/type/ddwtos && git fetch origin && git pull origin master)