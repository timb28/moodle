#!/bin/bash
##
## Shell script to download all the third party Moodle plugins used by Harcourts Academy
##

## Atto plugins ##
./atto/atto_fullscreen-install.sh
./atto/atto_htmlplus-install.sh
./atto/atto_styles-install.sh

## Authentication ##
./auth/auth_lenauth-install.sh

## Blocks ##
./blocks/block_configurable_report-install.sh
./blocks/block_forum_aggregator-install.sh
./blocks/block_forum_post-install.sh
./blocks/block_quickmail-install.sh
./blocks/block_unanswered_discussions-install.sh

## Course Formats ##
./course/format_flexsections-install.sh

## Enrolments ##
./enrol/enrol_apply-install.sh
./enrol/enrol_metamnet-install.sh

## Activities
./mod/mod_certificate-install.sh
./mod/mod_h5p-install.sh
./mod/mod_game-install.sh
./mod/mod_questionnaire-install.sh
./mod/mod_subcourse-install.sh
./mod/mod_videofile-install.sh

## Quiz Question Types
./question/qtype_oumultiresponse-install.sh
./question/qtype_pmatch-install.sh
