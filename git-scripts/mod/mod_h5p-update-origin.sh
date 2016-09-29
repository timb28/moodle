## H5P
echo "Updating H5P Module"
(cd ../mod/hvp && git fetch origin && git checkout 1.0-rc.3)
(cd ../mod/hvp && git submodule update --recursive)
