#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Multiple Response Quiz Question Type
echo "Cloning Multiple Choice, Multiple Response Question Type from Origin"
(cd ../../question/type/oumultiresponse && git fetch origin && git pull origin master)