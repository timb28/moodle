echo "Updating Quickmain Block"
(cd ../blocks/quickmail/ && git fetch upstream)
(cd ../blocks/quickmail/ && git merge master)