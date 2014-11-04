#!/bin/bash
echo "Updating Unanswered Discussions Block"
(cd ../blocks/unanswered_discussions/ && git fetch origin && git pull origin master)