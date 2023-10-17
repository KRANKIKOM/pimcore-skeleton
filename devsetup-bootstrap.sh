#!/usr/bin/env bash

echo "**** Running direnv allow"

which direnv >/dev/null
if [ $? == "1" ]; then
   echo "ERROR: 'direnv' is not installed. For Installation Guide see https://direnv.net/docs/installation.html"
   exit 3
fi
direnv allow

echo "**** Creating proxy network if required"
docker network list|grep proxy || docker network create proxy 2>&1 >/dev/null

./devsetup-composer-install.sh

if [ -f ./vendor/krankikom/pimcore-jetpakk/devsetup/devsetup-bootstrap.sh ]; then
	./vendor/krankikom/pimcore-jetpakk/devsetup/devsetup-bootstrap.sh
fi