## Checklist
echo "Cloning Certificates Module"
(cd .. && git clone https://github.com/davosmith/moodle-checklist.git mod/checklist)
(cd .. && git --git-dir=mod/checklist/.git remote add upstream https://github.com/davosmith/moodle-checklist.git)
(cd .. && echo /mod/checklist/ >> .git/info/exclude)
## (cd ../mod/checklist && git branch -a)
## (cd ../mod/checklist && git branch --track HA-Moodle25 origin/HA-Moodle25)
## (cd ../mod/checklist && git checkout HA-Moodle25) 