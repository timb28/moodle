echo "Updating Auto Enrolment Plugin from Origin"
(cd ../enrol/auto/ && git fetch origin && git checkout MOODLE_29_STABLE && git pull)