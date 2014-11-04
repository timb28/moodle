#!/bin/bash
echo "Updating Quickmail Block"
(cd ../../blocks/quickmail/ && git fetch origin && git pull upstream HA-Moodle27)