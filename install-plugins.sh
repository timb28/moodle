##
## This script downloads all the third party Moodle plugins used by Harcourts Academy
##

# Certificate
git clone https://github.com/timb28/moodle-mod_certificate.git mod/certificate
git --git-dir=mod/certificate/.git remote add upstream https://github.com/markn86/moodle-mod_certificate.git
echo /mod/certificate/ >> .git/info/exclude
 
# Game
git clone https://github.com/timb28/moodle-mod_game.git mod/game
echo /mod/game/ >> .git/info/exclude
cd mod/game
git remote add upstream https://github.com/bdaloukas/moodle-mod_game.git
git branch -a
git branch --track MOODLE_20_STABLE origin/MOODLE_20_STABLE
git checkout MOODLE_20_STABLE
cd ../..
 
# Questionnaire
git clone https://github.com/timb28/moodle-mod_questionnaire.git mod/questionnaire
echo /mod/questionnaire/ >> .git/info/exclude
cd mod/questionnaire
git remote add upstream https://github.com/remotelearner/moodle-mod_questionnaire.git
git branch -a
git branch --track MOODLE_24_STABLE origin/MOODLE_24_STABLE
git checkout MOODLE_24_STABLE
cd ../..
 
# Configurable Reports
git clone https://github.com/timb28/moodle-block_configurablereports.git blocks/configurable_reports
echo /blocks/configurable_reports/ >> .git/info/exclude
git --git-dir=blocks/configurable_reports/.git remote add upstream https://github.com/jleyva/moodle-block_configurablereports.git
 
# OU Drag and Drop Images Quiz Question Type
git clone https://github.com/timb28/moodle-qtype_ddimageortext.git question/type/ddimageortext
echo /question/type/ddimageortext/ >> .git/info/exclude
git --git-dir=question/type/ddimageortext/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddimageortext.git
 
# OU Drag and Drop Text Quiz Question Type
git clone https://github.com/timb28/moodle-qtype_ddwtos.git question/type/ddwtos
echo /question/type/ddwtos/ >> .git/info/exclude
git --git-dir=question/type/ddwtos/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddwtos.git
 
# OU Select Missing Words Quiz Question Type
git clone https://github.com/timb28/moodle-qtype_gapselect.git question/type/gapselect
echo /question/type/gapselect/ >> .git/info/exclude
git --git-dir=question/type/gapselect/.git remote add upstream https://github.com/moodleou/moodle-qtype_gapselect.git
 
# OU Multiple Response Quiz Question Type
git clone https://github.com/timb28/moodle-qtype_oumultiresponse.git question/type/oumultiresponse
echo /question/type/oumultiresponse/ >> .git/info/exclude
git --git-dir=question/type/oumultiresponse/.git remote add upstream https://github.com/moodleou/moodle-qtype_oumultiresponse.git