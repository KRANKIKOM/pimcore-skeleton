#!/usr/bin/env bash

PIMCORE_IMAGE_LABEL=PHP8.1-fpm

while getopts hv:n:p: option
do
 case "${option}"
 in
    h) echo "Usage: $0 [-n NAME_OF_THE_REPO] [-v VAGRANT_HOSTNAME] [-p PHP_IMAGE_LABEL (defaults to PHP8.1-fpm)]"; echo "Omitting -n and -v will cause the script to try to regenerate .envrc based on values from a pre-existing .envrc";exit;;
    n) NAME="${OPTARG}";;
    p) PIMCORE_IMAGE_LABEL="${OPTARG}";;
    v) VAGRANT_HOSTNAME="${OPTARG}";;
 esac
done

if [ -z "$NAME" ]; then
    echo "*** Attempting to regenerate based on existing .envrc"
    if [ -f ".envrc" ]; then
        source .envrc
        if [ ! -z "$VAGRANT_DNS" ]; then
            VAGRANT_HOSTNAME=$VAGRANT_DNS
            NAME=$DOCKER_IMAGE_NAME
        fi
    else
        echo "*** No existing .envrc found!"
    fi
fi

if [ -z "$NAME" ]; then
    echo "Error: Specify a NAME with -n";
    exit 1;
fi

if [ -z "$VAGRANT_HOSTNAME" ]; then
    VAGRANT_HOSTNAME="${NAME}.test"
fi


echo "**** Adding .envrc"
RAND1=$(( $RANDOM % 50 + 1 ))
RAND2=$(( $RANDOM % 50 + 1 ))
MYSQL_LOCAL_PORT=`awk -v min=10000 -v max=30000 'BEGIN{srand(); print int(min+rand()*(max-min+1))}'`
ESH_VARS="VAGRANT_HOSTNAME=$VAGRANT_HOSTNAME RAND1=$RAND1 RAND2=$RAND2 WEB_NAME=$NAME MYSQL_LOCAL_PORT=$MYSQL_LOCAL_PORT PIMCORE_IMAGE_LABEL=$PIMCORE_IMAGE_LABEL DOCKER_COMPOSE_CMD=$DOCKER_COMPOSE_CMD"
ESH_BASEPATH="vendor/krankikom/pimcore-jetpakk/"
if [ ! -d "$ESH_BASEPATH" ]; then
    ESH_BASEPATH="."
fi

ESH="$ESH_BASEPATH/containers/esh"
ESH_TPL="$ESH_BASEPATH/.envrc.esh"

echo $ESH $ESH_TPL $ESH_VARS
$ESH $ESH_TPL $ESH_VARS > .envrc
