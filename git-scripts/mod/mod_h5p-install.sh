## Game
echo "Cloning H5P Module"
(cd .. && git clone https://github.com/h5p/h5p-moodle-plugin.git mod/hvp)
(cd .. && cd mod/hvp && git submodule update --init)
(cd .. && echo /mod/hvp/ >> .git/info/exclude)
(cd ../mod/hvp && git checkout 1.0-rc.2)
