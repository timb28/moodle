##
## Shell script to update Moodle plugins via Git 
## 

## Certificates
echo "Updating Certificates Module"
(cd ../mod/certificate/ && git pull upstream master)

## Configurable Reports
echo "Updating Configurable Reports Block"
(cd ../blocks/configurable_reports && git pull upstream master)

## Game
echo "Updating Game Module"
(cd ../mod/game && git pull upstream master)

## Questionnaire
echo "Updating Questionnaire Module"
(cd ../mod/questionnaire/ && git pull upstream MOODLE_25_STABLE)

## Quiz Question Types

## Drag and Drop Image or Text
echo "Updating Drag and Drop Image or Text Question Type"
(cd ../question/type/ddimageortext && git pull upstream master)

## Drag and Drop Text
echo "Updating Drag and Drop Text Question Type"
(cd ../question/type/ddwtos && git pull upstream master)

## Gap Select (required by Drag and Drop question types)
echo "Updating Gap Select Question Type"
(cd ../question/type/gapselect && git pull upstream master)

## Multiple Choice, Multiple Response
echo "Updating Multiple Choice, Multiple Response Question Type"
(cd ../question/type/oumultiresponse && git pull upstream master)
