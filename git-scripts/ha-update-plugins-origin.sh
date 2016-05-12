#!/bin/bash
##
## Shell script to update Moodle plugins via Git 
## 

## Atto plugins ##
./atto/atto_fullscreen-update-origin.sh
./atto/atto_htmlplus-update-origin.sh
./atto/atto_styles-update-origin.sh

## Authentication ##
./auth/auth_lenauth-update-origin.sh

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
./enrol/enrol_metamnet-update-origin.sh

## Activities ##
./mod/mod_certificate-update-origin.sh
./mod/mod_game-update-origin.sh
./mod/mod_questionnaire-update-origin.sh
./mod/mod_subcourse-update-origin.sh
./mod/mod_videofile-update-origin.sh

## Quiz Question Types ##
./question/qtype_oumultiresponse-update-origin.sh
./question/qtype_pmatch-update-origin.sh