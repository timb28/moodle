## Game
echo "Updating H5P Module"
(cd ../mod/hvp && git fetch origin && git checkout master)
(cd ../mod/hvp && git submodule update --recursive)
