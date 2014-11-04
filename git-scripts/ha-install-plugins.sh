#!/bin/bash
##
## Shell script to download all the third party Moodle plugins used by Harcourts Academy
##

## Blocks ##
./block/block_configurable_report-install.sh
./blocks/block_forum_aggregator-install.sh
./blocks/block_forum_post-install.sh
./blocks/block_quickmail-install.sh
./blocks/block_unanswered_discussions-install.sh

## Course Formats ##
./course/format_flexsections-install.sh

## Enrolments ##
./enrol/enrol_apply-install.sh

## Activities
./mod/mod_certificate-install.sh
./mod/mod_game-install.sh
./mod/mod_questionnaire-install.sh
./mod/mod_videofile-install.sh

## Quiz Question Types
./question/qtype_ddimageortext-install.sh
./question/qtype_ddwtos-install.sh
./question/qtype_gapselect-install.sh
./question/qtype_oumultiresponse-install.sh
./question/qtype_pmatch-install.sh