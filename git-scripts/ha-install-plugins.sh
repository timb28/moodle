##
## Shell script to download all the third party Moodle plugins used by Harcourts Academy
##

## Certificates
echo "Cloning Certificates Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_certificate.git mod/certificate)
(cd .. && git --git-dir=mod/certificate/.git remote add upstream https://github.com/markn86/moodle-mod_certificate.git)
(cd .. && echo /mod/certificate/ >> .git/info/exclude)
(cd ../mod/certificate && git branch -a)
(cd ../mod/certificate && git branch --track HA-Moodle25 origin/HA-Moodle25)
(cd ../mod/certificate && git checkout HA-Moodle25)
 
## Configurable Reports
echo "Cloning Configurable Reports Block"
(cd .. && git clone https://github.com/timb28/moodle-block_configurablereports.git blocks/configurable_reports)
(cd .. && echo /blocks/configurable_reports/ >> .git/info/exclude)
(cd .. && git --git-dir=blocks/configurable_reports/.git remote add upstream https://github.com/jleyva/moodle-block_configurablereports.git)
 
## Game
echo "Cloning Game Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_game.git mod/game)
(cd .. && echo /mod/game/ >> .git/info/exclude)
(cd ../mod/game && git remote add upstream https://github.com/bdaloukas/moodle-mod_game.git)
(cd ../mod/game && git branch -a)
(cd ../mod/game && git branch --track MOODLE_20_STABLE origin/MOODLE_20_STABLE)
(cd ../mod/game && git checkout MOODLE_20_STABLE)
 
## Questionnaire
echo "Cloning Questionnaire Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_questionnaire.git mod/questionnaire)
(cd .. && echo /mod/questionnaire/ >> .git/info/exclude)
(cd ../mod/questionnaire && git remote add upstream https://github.com/remotelearner/moodle-mod_questionnaire.git)
(cd ../mod/questionnaire && git branch -a)
(cd ../mod/questionnaire && git branch --track MOODLE_25_STABLE origin/MOODLE_25_STABLE)
(cd ../mod/questionnaire && git checkout MOODLE_25_STABLE)

## Themes

## Essential
echo "Cloning Essential Theme"
(cd .. && git clone https://github.com/timb28/moodle-theme_essential.git theme/essential)
(cd .. && echo /theme/essential/ >> .git/info/exclude)
(cd ../theme/essential/ && git remote add upstream https://github.com/moodleman/moodle-theme_essential.git)
(cd ../theme/essential/ && git branch -a)
(cd ../theme/essential/ && git branch --track MOODLE_25 origin/MOODLE_25)
(cd ../theme/essential/ && git checkout MOODLE_25)

## Quiz Question Types

## OU Drag and Drop Images Quiz Question Type
echo "Cloning Drag and Drop Image or Text Question Type"
(cd .. && git clone https://github.com/timb28/moodle-qtype_ddimageortext.git question/type/ddimageortext)
(cd .. && echo /question/type/ddimageortext/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddimageortext/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddimageortext.git)
 
## OU Drag and Drop Text Quiz Question Type
echo "Cloning Drag and Drop Text Question Type"
(cd .. && git clone https://github.com/timb28/moodle-qtype_ddwtos.git question/type/ddwtos)
(cd .. && echo /question/type/ddwtos/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddwtos/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddwtos.git)
 
## OU Select Missing Words Quiz Question Type
echo "Cloning Gap Select Question Type"
(cd .. && git clone https://github.com/timb28/moodle-qtype_gapselect.git question/type/gapselect)
(cd .. && echo /question/type/gapselect/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/gapselect/.git remote add upstream https://github.com/moodleou/moodle-qtype_gapselect.git)
 
## OU Multiple Response Quiz Question Type
echo "Updating Multiple Choice, Multiple Response Question Type"
(cd .. && git clone https://github.com/timb28/moodle-qtype_oumultiresponse.git question/type/oumultiresponse)
(cd .. && echo /question/type/oumultiresponse/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/oumultiresponse/.git remote add upstream https://github.com/moodleou/moodle-qtype_oumultiresponse.git )
