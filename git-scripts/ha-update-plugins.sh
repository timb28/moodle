##
## Shell script to update Moodle plugins via Git 
## 

## Certificates
echo "Updating Certificates Module"
(cd ../mod/certificate/ && git fetch origin && git pull origin MOODLE_25_STABLE)

## Configurable Reports
## Blocks ##
echo "Updating Block Forum Aggregator"
./block_forum_aggregator-update.sh

echo "Updating Block Forum Post"
./block_forum_post-update.sh

echo "Updating Block Quickmail"
./block_quickmail-update.sh

echo "Updating Block Unanswered Discussions"
./block_unanswered_discussions-install.sh

## Enrolments ##
echo "Updating Enrol Application"
./enrol_apply-update.sh

## Course Formats ##
echo "Updating Course Format Flexsections"
./format_flexsections-update.sh

## Activities ##
echo "Updating Certificates Module"
(cd ../mod/certificate/ && git fetch origin && git pull origin MOODLE_25_STABLE)

echo "Updating Configurable Reports Block"
(cd ../blocks/configurable_reports && git fetch origin && git pull origin CR_23_STABLE && git checkout CR_23_STABLE)

## Questionnaire
echo "Updating Activity Game"
./mod_game-update.sh
























echo "Updating Questionnaire Module"
(cd ../mod/questionnaire/ && git fetch origin && git pull origin MOODLE_25_STABLE)

## Themes
echo "Updating Activity Videofile"
./mod_videofile-update.sh

## Themes ##
echo "Updating Essential Theme"
(cd ../theme/essential && git fetch origin && git pull origin ESSENTIAL_254)

## Quiz Question Types
## Quiz Question Types ##

## Drag and Drop Image or Text
echo "Updating Drag and Drop Image or Text Question Type"
(cd ../question/type/ddimageortext && git fetch origin && git pull origin master)

## Drag and Drop Text
echo "Updating Drag and Drop Text Question Type"
(cd ../question/type/ddwtos && git fetch origin && git pull origin master)

## Gap Select (required by Drag and Drop question types)
echo "Updating Gap Select Question Type"
(cd ../question/type/gapselect && git fetch origin && git pull origin master)

## Multiple Choice, Multiple Response
echo "Cloning Multiple Choice, Multiple Response Question Type"
(cd ../question/type/oumultiresponse && git fetch origin && git pull origin master)
