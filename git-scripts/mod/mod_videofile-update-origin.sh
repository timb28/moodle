#!/bin/bash
echo "Updating VideoFile Module from Origin"
(cd ../../mod/videofile/ && git fetch origin)
(cd ../../mod/videofile/ && git checkout origin/HA-VideoFileCoursePlayer)