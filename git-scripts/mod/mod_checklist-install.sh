## Checklist
echo "Cloning Checklist Module"
(cd .. && git clone https://github.com/davosmith/moodle-checklist.git mod/checklist)
(cd .. && git --git-dir=mod/checklist/.git remote add upstream https://github.com/davosmith/moodle-checklist.git)
(cd .. && echo /mod/checklist/ >> .git/info/exclude)
