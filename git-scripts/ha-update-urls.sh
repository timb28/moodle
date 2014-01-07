##
## Shell script to update all Github urls to the new Harcourts Academy organisation account
##

echo "Updating Github URLs"

## Certificate
echo "Certificate Module"
(cd .. && git --git-dir=mod/certificate/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-mod_certificate.git)

## Configurable Reports
echo "Configurable Reports Block"
(cd .. && git --git-dir=blocks/configurable_reports/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-block_configurablereports.git)

## Game
echo "Game Module"
(cd .. && git --git-dir=mod/game/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-mod_game.git)

## Questionnaire
echo "Questionnaire Module"
(cd .. && git --git-dir=mod/questionnaire/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-mod_questionnaire.git)

## Quiz Question Types

## OU Drag and Drop Images Quiz Question Type
echo "Drag and Drop Image or Text Question Type"
(cd .. && git --git-dir=question/type/ddimageortext/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-qtype_ddimageortext.git)

## OU Drag and Drop Text Quiz Question Type
echo "Drag and Drop Text Question Type"
(cd .. && git --git-dir=question/type/ddwtos/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-qtype_ddwtos.git)
 
## OU Select Missing Words Quiz Question Type
echo "Gap Select Question Type"
(cd .. && git --git-dir=question/type/gapselect/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-qtype_gapselect.git)
 
## OU Multiple Response Quiz Question Type
echo "Multiple Choice, Multiple Response Question Type"
(cd .. && git --git-dir=question/type/oumultiresponse/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-qtype_oumultiresponse.git)

## Videofile
echo "Videofile Module"
(cd .. && git --git-dir=mod/videofile/.git remote set-url origin https://github.com/HarcourtsAcademy/moodle-mod_videofile.git)