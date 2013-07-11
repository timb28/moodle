##
## Shell script to download all the third party Moodle plugins used by Harcourts Academy
##

## Certificates
echo "Cloning Certificates Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_certificate.git mod/certificate)
(cd .. && git --git-dir=mod/certificate/.git remote add upstream https://github.com/markn86/moodle-mod_certificate.git)
(cd .. && echo /mod/certificate/ >> .git/info/exclude)
 
## Configurable Reports
echo "Cloning Configurable Reports Block"
(cd .. && git clone https://github.com/timb28/moodle-block_configurablereports.git blocks/configurable_reports)
(cd .. && echo /blocks/configurable_reports/ >> .git/info/exclude)
(cd .. && git --git-dir=blocks/configurable_reports/.git remote add upstream https://github.com/jleyva/moodle-block_configurablereports.git)
 
## Game
echo "Cloning Game Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_game.git mod/game)
(cd .. && echo /mod/game/ >> .git/info/exclude)
(cd .. && cd mod/game)
(cd .. && git remote add upstream https://github.com/bdaloukas/moodle-mod_game.git)
(cd .. && git branch -a)
(cd .. && git branch --track MOODLE_20_STABLE origin/MOODLE_20_STABLE)
(cd .. && git checkout MOODLE_20_STABLE)
 
## Questionnaire
echo "Cloning Questionnaire Module"
(cd .. && git clone https://github.com/timb28/moodle-mod_questionnaire.git mod/questionnaire)
(cd .. && echo /mod/questionnaire/ >> .git/info/exclude)
(cd .. && cd mod/questionnaire)
(cd .. && git remote add upstream https://github.com/remotelearner/moodle-mod_questionnaire.git)
(cd .. && git branch -a)
(cd .. && git branch --track MOODLE_24_STABLE origin/MOODLE_24_STABLE)
(cd .. && git checkout MOODLE_24_STABLE)

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
