#!/bin/bash
echo "Updating Quickmail Block"
(cd ../blocks/quickmail/ && git fetch origin && git checkout HA-Moodle30 && git pull)