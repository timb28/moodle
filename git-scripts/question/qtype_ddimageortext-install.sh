#!/bin/bash
## Used by:
## - AU Sales Registration
##
## OU Drag and Drop Images Quiz Question Type
echo "Cloning Drag and Drop Image or Text Question Type"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-qtype_ddimageortext.git question/type/ddimageortext)
(cd .. && echo /question/type/ddimageortext/ >> .git/info/exclude)
(cd .. && git --git-dir=question/type/ddimageortext/.git remote add upstream https://github.com/moodleou/moodle-qtype_ddimageortext.git)
(cd ../question/type/ddimageortext && git branch -a)
(cd ../question/type/ddimageortext && git branch --track master origin/master)
(cd ../question/type/ddimageortext && git checkout master)