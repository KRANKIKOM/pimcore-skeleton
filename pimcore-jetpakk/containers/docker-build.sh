#!/bin/bash

#set -e

SCRIPT_DIR=$( cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )
source "$SCRIPT_DIR/.envrc"
unset DOCKER_HOST

PUSH="false"
NOCACHE=""
NON_INTERACTIVE="false"
ONLY_UPDATE_DOCKERFILE="false"
while getopts onv:n:r:s:pilm option
do
 case "${option}"
 in
 v) VERSION="${OPTARG}";;
 r) DOCKER_REGISTRY="${OPTARG}";;
 i) NON_INTERACTIVE="true";;
 s) SERVICE="${OPTARG}";;
 p) PUSH="true";;
 n) NOCACHE="--no-cache";;
 o) ONLY_UPDATE_DOCKERFILE="true";;
 l) LOCAL="true";;
 esac
done

if [ "$VERSION" == "" ]; then
    VERSION="latest"
fi

if [ "$ONLY_UPDATE_DOCKERFILE" == "false" ]; then

  if [ -z "$DOCKER_IMAGE_NAME" ]; then
    echo "ERROR: No DOCKER_IMAGE_NAME specified -> please either set env-var DOCKER_IMAGE_NAME manually or define it in .envrc"
    exit 2
  fi
  if [ -z "$DOCKER_IMAGE_PROJECT" ]; then
    echo "ERROR: No DOCKER_IMAGE_PROJECT specified -> please either set env-var DOCKER_IMAGE_PROJECT manually or define it in .envrc"
    exit 2
  fi

fi

if [ -z "$ASSETS_DIR" ]; then
  export ASSETS_DIR="webpack-assets"
fi

if [ -z "$DOCKER_REGISTRY" ]; then
  DOCKER_REGISTRY="k-registry.krankikom.de"
fi

DOCKER_IMAGE_NAME_FULL="${DOCKER_REGISTRY}/${DOCKER_IMAGE_PROJECT}/${DOCKER_IMAGE_NAME}:${VERSION}"

if [ "$LOCAL" = "true" ]; then
  unset DOCKER_REGISTRY
  unset DOCKER_HOST
  DOCKER_IMAGE_NAME_FULL="forplaner:${VERSION}"
fi

if [ -z "$PIMCORE_IMAGE_LABEL" ]; then
  # We default to PHP8.0-fpm here so we don't break legacy projects
  # The default .envrc has the actual default for new projects
  PIMCORE_IMAGE_LABEL="PHP8.0-fpm" 
fi

if [ -z "$GOTENBERG" ]; then
  GOTENBERG="false"
fi


DOCKER_UID=$(id -u)
DOCKER_GID=$(id -g)


ESH_VARS="DOCKER_COMPOSE_BUILD=false ASSETS_DIR=${ASSETS_DIR} FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR} PIMCORE_IMAGE_LABEL=${PIMCORE_IMAGE_LABEL} DOCKER_UID= DOCKER_GID="
vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/php-fpm/Dockerfile $ESH_VARS > Dockerfile

ESH_VARS="DOCKER_COMPOSE_BUILD=true ASSETS_DIR=${ASSETS_DIR} FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR} PIMCORE_IMAGE_LABEL=${PIMCORE_IMAGE_LABEL} DOCKER_UID=$DOCKER_UID DOCKER_GID=$DOCKER_GID"
vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/php-fpm/Dockerfile $ESH_VARS > Dockerfile.compose

if [[ $PIMCORE_IMAGE_LABEL =~ ^(php[0-9]+\.[0-9]+)(-[^-]+)?-v[0-9]+$ ]]; then
    # Handle format phpX.Y-something-vZ
    php_version="${PIMCORE_IMAGE_LABEL%%-*}"
    version="${PIMCORE_IMAGE_LABEL##*-}"
    PIMCORE_IMAGE_LABEL_DEBUG="${php_version}-debug-${version}"
elif [[ $PIMCORE_IMAGE_LABEL =~ ^(PHP[0-9]+\.[0-9]+-fpm)$ ]]; then
    # Handle format phpX.Y-fpm
    PIMCORE_IMAGE_LABEL_DEBUG="${PIMCORE_IMAGE_LABEL}-debug"
else
    echo "Unexpected image label format, can't create debug image based on PIMCORE_IMAGE_LABEL=$PIMCORE_IMAGE_LABEL"
    exit 1
fi

if [ ! -z "$PIMCORE_IMAGE_LABEL_DEBUG" ]; then
  ESH_VARS="DOCKER_COMPOSE_BUILD=true ASSETS_DIR=${ASSETS_DIR} FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR} PIMCORE_IMAGE_LABEL=${PIMCORE_IMAGE_LABEL_DEBUG} DOCKER_UID=$DOCKER_UID DOCKER_GID=$DOCKER_GID"
  vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/php-fpm/Dockerfile $ESH_VARS > Dockerfile.debug.compose
fi

ESH_VARS="DOCKER_COMPOSE_BUILD=false ASSETS_DIR=${ASSETS_DIR} FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR} PIMCORE_IMAGE_LABEL=${PIMCORE_IMAGE_LABEL} DOCKER_UID= DOCKER_GID="
vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/apache/Dockerfile $ESH_VARS > Dockerfile.apache
ESH_VARS="DOCKER_COMPOSE_BUILD=true ASSETS_DIR=${ASSETS_DIR} FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR} PIMCORE_IMAGE_LABEL=${PIMCORE_IMAGE_LABEL} DOCKER_UID=$DOCKER_UID DOCKER_GID=$DOCKER_GID"
vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/apache/Dockerfile $ESH_VARS > Dockerfile.apache.compose


if [ "$USER" = "gitpod" ]; then
  ELASTICSEARCH_MEMLOCK="false"
  ALLOW_PORT80="true"
else
  ELASTICSEARCH_MEMLOCK="true"
  ALLOW_PORT80="false"
fi

USE_TRAEFIK="false"
if [ "$PIMCORE_DEVSETUP_NATIVEDOCKER" == "1" ] && [ "$USER" != "gitpod" ]; then
  USE_TRAEFIK="true"
fi

if [ "$VAGRANT_IP" ]; then
  ESH_VARS_COMPOSE="GOTENBERG=$GOTENBERG MARIADB_VOLUME_PATH=pimcore-mariadb-10 MAIN_VOLUME_PATH=/opt/www ELASTICSEARCH_MEMLOCK=$ELASTICSEARCH_MEMLOCK ELASTICSEARCH_VOLUME_PATH=elasticsearch-data ALLOW_PORT80=$ALLOW_PORT80 WEB_NAME=$WEB_NAME USE_TRAEFIK=$USE_TRAEFIK MYSQL_LOCAL_PORT=$MYSQL_LOCAL_PORT DOCKER_UID= DOCKER_GID= FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR}"
elif [ "$USER" = "gitpod" ]; then
  ESH_VARS_COMPOSE="GOTENBERG=$GOTENBERG MARIADB_VOLUME_PATH=/workspace/mariadb-data MAIN_VOLUME_PATH=./ ELASTICSEARCH_MEMLOCK=$ELASTICSEARCH_MEMLOCK ELASTICSEARCH_VOLUME_PATH=/workspace/elasticsearch-data ALLOW_PORT80=$ALLOW_PORT80 WEB_NAME=$WEB_NAME USE_TRAEFIK=$USE_TRAEFIK MYSQL_LOCAL_PORT=$MYSQL_LOCAL_PORT DOCKER_UID=$DOCKER_UID DOCKER_GID=$DOCKER_GID FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR}"  
else
  ESH_VARS_COMPOSE="GOTENBERG=$GOTENBERG MARIADB_VOLUME_PATH=pimcore-mariadb-10 MAIN_VOLUME_PATH=./ ELASTICSEARCH_MEMLOCK=$ELASTICSEARCH_MEMLOCK ELASTICSEARCH_VOLUME_PATH=elasticsearch-data ALLOW_PORT80=$ALLOW_PORT80 WEB_NAME=$WEB_NAME USE_TRAEFIK=$USE_TRAEFIK  DOCKER_UID=$DOCKER_UID DOCKER_GID=$DOCKER_GID MYSQL_LOCAL_PORT=$MYSQL_LOCAL_PORT FRONTEND_BUILD_FLAVOUR=${FRONTEND_BUILD_FLAVOUR}"
fi
vendor/krankikom/pimcore-jetpakk/containers/esh vendor/krankikom/pimcore-jetpakk/containers/docker-compose.yml.esh $ESH_VARS_COMPOSE > docker-compose.yml



if [ "$ONLY_UPDATE_DOCKERFILE" == "true" ]; then
  exit 0
fi

if [ "$PUSH" = "false" ]; then
  export DOCKER_BUILDKIT=1
  docker build --build-arg BUILDKIT_INLINE_CACHE=1 $NOCACHE -f ./Dockerfile -t ${DOCKER_IMAGE_NAME_FULL} .

  if [ "$NON_INTERACTIVE" = "false" ]; then
    echo -en "Do you want to push? (y/N) "; read -n 1 -t 10 ask || exit 0
    if [ "$ask" = "y" ]; then
      echo " "
      docker push ${DOCKER_IMAGE_NAME_FULL}
    fi
  fi

else
  docker push ${DOCKER_IMAGE_NAME_FULL}
  # delete all images, but keep the current version as cache layer for the next build
  echo "List Docker images"
  docker images|grep "${DOCKER_IMAGE_NAME_FULL}"|grep -v " ${VERSION} "|awk '{print $3}'|uniq
  for i in $(docker images|grep "${DOCKER_IMAGE_NAME_FULL}"|grep -v " ${VERSION} "|awk '{print $3}'|uniq); do
    echo "Docker image delete: $i"
    docker rmi --force $i
  done
fi
set +e
