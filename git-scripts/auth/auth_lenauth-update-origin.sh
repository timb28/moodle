#!/bin/bash
## Used by:
## - Global Moodle public students
##
echo "Updating LenAuth authentication module from Origin"
(cd ../auth/lenauth && git pull origin)
