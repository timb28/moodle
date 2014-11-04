#!/bin/bash
echo "Updating Forum Post Block"
(cd ../../blocks/forum_post/ && git fetch origin && git pull origin master)