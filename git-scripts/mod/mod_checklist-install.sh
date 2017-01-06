## Checklist
echo "Cloning Checklist Module"
(cd .. && git clone https://github.com/davosmith/moodle-checklist.git mod/checklist)
(cd .. && echo /mod/checklist/ >> .git/info/exclude)
#(cd ../mod/subcourse && git branch --track HA-Moodle30 origin/HA-Moodle30)
#(cd ../mod/subcourse && git checkout HA-Moodle30)
