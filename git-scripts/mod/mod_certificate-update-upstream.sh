#!/bin/bash
echo "Updating Certificates Module from Upstream"
(cd ../../mod/certificate/ && git fetch upstream)
(cd ../../mod/certificate/ && git checkout upstream/MOODLE_27_STABLE)
(cd ../../mod/certificate/ && git merge HA-Moodle27)