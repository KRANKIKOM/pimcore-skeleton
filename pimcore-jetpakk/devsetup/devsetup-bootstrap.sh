#!/usr/bin/env bash



# This script gets called by a script with the same name in the project root which should
# exist in our projects created from krankikom/pimcore-skeleton.
# The previous script has already installed composer dependencies and executed "direnv allow"

echo "using ${DOCKER_COMPOSE_CMD}"

if [ ! -d ~/.composer ]; then
    echo  "**** Creating local composer cache/config dir"
    mkdir -p ~/.composer
fi

echo "**** Generating Dockerfiles/compose file"    
./docker-build.sh -o

# the pimcore-init service has a dependency on db, however I've observed that docker-compose often doesn't actually bring it up
echo "**** Bringing up db & redis"    
$DOCKER_COMPOSE_CMD up -d db redis
sleep 10

echo "**** Running pimcore-init"    
$DOCKER_COMPOSE_CMD up pimcore-init