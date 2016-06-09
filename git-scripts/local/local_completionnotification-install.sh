echo "Cloning Local Completion Notification Plugin"
(cd .. && git clone https://github.com/HarcourtsAcademy/moodle-local_completionnotification.git local/completionnotification)
(cd .. && echo /local/completionnotification/ >> .git/info/exclude)
#(cd ../local/completionnotification && git branch --track master origin/master)
#(cd ../local/completionnotification && git checkout master)