#!/bin/bash
echo "Updating Certificates Module from Upstream"
(cd ../mod/certificate/ && git fetch upstream)
(cd ../mod/certificate/ && git checkout upstream/MOODLE_28_STABLE)
(cd ../mod/certificate/ && git checkout -b HA-Moodle28)
(cd ../mod/certificate/ && git merge origin/HA-Moodle27)