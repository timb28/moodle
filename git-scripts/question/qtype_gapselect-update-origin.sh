#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Select Missing Words Quiz Question Type
echo "Updating Gap Select Question Type from Origin"
(cd ../../question/type/gapselect && git fetch origin && git pull origin master)