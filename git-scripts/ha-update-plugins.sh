##
## Shell script to update Moodle plugins via Git 
## 

## Certificates
echo "Updating Certificates Module"
(cd ../mod/certificate/ && git fetch upstream && git pull upstream MOODLE_25_STABLE)

## Configurable Reports
echo "Updating Configurable Reports Block"
(cd ../blocks/configurable_reports && git fetch upstream && git pull upstream CR_23_STABLE && git checkout CR_23_STABLE)

## Game
echo "Updating Game Module"
(cd ../mod/game && git fetch upstream && git pull upstream MOODLE_20_STABLE)

## Questionnaire
echo "Updating Questionnaire Module"
(cd ../mod/questionnaire/ && git fetch upstream && git pull upstream MOODLE_25_STABLE)

## Themes
echo "Updating Essential Theme"
(cd ../theme/essential && git fetch upstream && git pull upstream ESSENTIAL_254)

## Quiz Question Types

## Drag and Drop Image or Text
echo "Updating Drag and Drop Image or Text Question Type"
(cd ../question/type/ddimageortext && git fetch upstream && git pull upstream master)

## Drag and Drop Text
echo "Updating Drag and Drop Text Question Type"
(cd ../question/type/ddwtos && git fetch upstream && git pull upstream master)

## Gap Select (required by Drag and Drop question types)
echo "Updating Gap Select Question Type"
(cd ../question/type/gapselect && git fetch upstream && git pull upstream master)

## Multiple Choice, Multiple Response
echo "Cloning Multiple Choice, Multiple Response Question Type"
(cd ../question/type/oumultiresponse && git fetch upstream && git pull upstream master)
