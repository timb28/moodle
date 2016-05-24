#!/bin/bash
echo "Updating Certificates Module from Origin"
(cd ../mod/certificate/ && git fetch origin && git checkout HA-Moodle30 && git pull)