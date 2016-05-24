#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Multiple Response Quiz Question Type
echo "Updating Multiple Choice, Multiple Response Question Type from Origin"
(cd ../question/type/oumultiresponse && git fetch origin && git checkout HA-Moodle30 && git pull)