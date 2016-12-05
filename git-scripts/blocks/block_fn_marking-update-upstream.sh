echo "Updating NED Marker Block from Upstream"
(cd ../blocks/fn_marking/ && git fetch upstream && git checkout master && git pull)
