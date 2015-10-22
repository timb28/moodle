#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Updating OAuth2 authentication module from Origin"
(cd ../auth/googleoauth2 && git fetch origin && git checkout HA-Moodle28)
