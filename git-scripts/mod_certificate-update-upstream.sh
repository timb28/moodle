echo "Updating Certificates Module from Upstream"
(cd ../mod/certificate/ && git fetch upstream)

## 24/09/2014 Need to update following lines since we may need to checkout upstream first then rebase.
#(cd ../mod/certificate/ && git rebase --onto upstream/MOODLE_25_STABLE HA-Moodle25)
#(cd ../mod/certificate/ && git checkout upstream/MOODLE_25_STABLE)
#(cd ../mod/certificate/ && git merge HA-Moodle25)