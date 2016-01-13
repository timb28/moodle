#!/bin/bash
##
## Shell script to update Moodle plugins via Git 
## 

## Blocks ##
./blocks/block_configurable_report-update-origin.sh
./blocks/block_forum_aggregator-update-origin.sh
./blocks/block_forum_post-update-origin.sh
./blocks/block_quickmail-update-origin.sh
./blocks/block_unanswered_discussions-update-origin.sh

## Course Formats ##
./course/format_flexsections-update-origin.sh

## Enrolments ##
./enrol/enrol_apply-update-origin.sh

## Activities ##
./mod/mod_certificate-update-origin.sh
./mod/mod_game-update-origin.sh
./mod/mod_questionnaire-update-origin.sh
./mod/mod_videofile-update-origin.sh

## Quiz Question Types ##
./question/qtype_ddimageortext-update-origin.sh
./question/qtype_ddwtos-update-origin.sh
./question/qtype_gapselect-update-origin.sh
./question/qtype_oumultiresponse-update-origin.sh
./question/qtype_pmatch-update-origin.sh