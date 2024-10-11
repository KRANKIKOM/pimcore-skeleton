#!/bin/bash

COMPOSER_REPO_URL="https://repo.packagist.com/krankikom/"
COMPOSER_AUTH_TOKEN="c8a7ecca281bc7a6682a01fc5cb406a73aabf86ed5ffa81285049e650b41"
COMPOSER_AUTH_ENVVAR="{\"http-basic\": {\"repo.packagist.com\": {\"username\": \"krankikom\", \"password\": \"$COMPOSER_AUTH_TOKEN\"}}}"

PIMCORE_IMAGE_LABEL="php8.2-max-v3"
DEFAULT_PIMCORE_SKELETON_VERSION="11.x-dev"
KUBERNETES_VERSION=""

while getopts hn:v:s:p:i:k: option
do
 case "${option}"
 in
    h) echo "Usage: $0 -n NAME_OF_THE_NEW_REPO -k KUBERNETES_VERSION (Kubernetes Version e.g. v1.23) [-p PIMCORE_IMAGE_LABEL (defaults to $PIMCORE_IMAGE_LABEL)] [-s PIMCORE_SKELETON_VERSION (defaults to '$DEFAULT_PIMCORE_SKELETON_VERSION') ]"; exit;;
    n) NAME="${OPTARG}";;
    v) VAGRANT_HOSTNAME="${OPTARG}";;
    s) PIMCORE_SKELETON_VERSION="${OPTARG}";;
    p) PIMCORE_IMAGE_LABEL="${OPTARG}";;
    i) PIMCORE_VERSION_OVERRIDE="${OPTARG}";;
    k) KUBERNETES_VERSION="${OPTARG}";;
 esac
done

if [ -z $PIMCORE_SKELETON_VERSION ]; then
    PIMCORE_SKELETON_VERSION="$DEFAULT_PIMCORE_SKELETON_VERSION"

fi

if [ -z $VAGRANT_HOSTNAME ]; then
    VAGRANT_HOSTNAME="$NAME.test"
fi

if [ -z $NAME ]; then
    echo "Please specify a name for the web directory, e.g. '-n sparkasse-web'"
    exit 1
fi

if [ -d "$NAME" ]; then
    echo "Directory $NAME already exists"
    exit 2
fi

if [ -z $KUBERNETES_VERSION ]; then
    echo "Please specify a Kubernetes Version e.g. '-k v1.23' these are current supported versions:"
    cd kubernetes; ls -1d v*; cd ..
    exit 1
fi

echo "**** Name '$NAME' - Devsetup repo: '$REPO' - Vagrant Hostname: '$VAGRANT_HOSTNAME' - Pimcore Skeleton: '$PIMCORE_SKELETON'"

if [ ! -d ~/.composer ]; then
    echo "**** Creating local composer cache/config dir"
    mkdir -p ~/.composer
fi

echo "**** Creating custom composer docker image"

DOCKERFILE_TMPDIR=`mktemp -d`
DOCKERFILE="$DOCKERFILE_TMPDIR/Dockerfile"
cat >$DOCKERFILE <<EOT
FROM composer
RUN docker-php-ext-install mysqli pdo pdo_mysql
EOT

docker build -t composer-pdo $DOCKERFILE_TMPDIR


echo "**** Preparing environment"
TMP_ENVFILE=$(mktemp)

cat <<EOT >> $TMP_ENVFILE
MYSQL_HOST=""
MYSQL_USER=""
MYSQL_PASSWORD=""
MYSQL_DB=""
REDIS_HOST=""
REDIS_PORT=""
COMPOSER_AUTH=$COMPOSER_AUTH_ENVVAR
COMPOSER_HOME=/composer
COMPOSER_MIRROR_PATH_REPOS=1
EOT

SKELETON_TMPDIR=$(mktemp -d)
SKELETON_COMPOSER_JSON="$SKELETON_TMPDIR/composer.json"
SKELETON_PACKAGES_JSON="$SKELETON_TMPDIR/packages.json"
cat <<EOT >$SKELETON_COMPOSER_JSON
{
  "name": "dummy/skel",
  "type": "project",
  "license": "GPL-3.0-or-later",
  "config": {
    "optimize-autoloader": true,
    "sort-packages": true,
    "process-timeout": 0,
    "allow-plugins": {
      "symfony/runtime": true
    }
  },
  "prefer-stable": false,
  "minimum-stability": "dev",
  "require": {
    "krankikom/pimcore-skeleton": "$PIMCORE_SKELETON_VERSION"
  },
  "repositories": {
    "private-packagist": {
        "type": "composer",
        "url": "https://repo.packagist.com/krankikom/"
    },
    "packagist.org": false
  }
}
EOT

cat <<EOT >$SKELETON_PACKAGES_JSON
{
    "packages": {
        "krankikom/pimcore-skeleton": {
            "$PIMCORE_SKELETON_VERSION": {
                "name": "krankikom/pimcore-skeleton",
                "version": "$PIMCORE_SKELETON_VERSION",
                "source": {
                    "url": "/skel",
                    "type": "git",
                    "reference": "master"
                }
            }
        }
    },
    "options": {
        "symlink": false
    }
}
EOT

if [ ! -z "$PIMCORE_VERSION_OVERRIDE" ] && [ "$PIMCORE_VERSION_OVERRIDE" != "current" ]; then
    OPWD=`pwd`
    echo "**** Using PIMCORE_VERSION_OVERRIDE=$PIMCORE_VERSION_OVERRIDE, creating manipulated skeleton"

    cd $SKELETON_TMPDIR
    echo "* Running composer install for initializing dummy skeleton"
    docker run --rm -it -v ~/.ssh:/root/.ssh -v ~/.composer:/composer --env-file $TMP_ENVFILE -v ${PWD}:/app --user $(id -u):$(id -g) composer-pdo install --ignore-platform-reqs
    echo "* Manipulating composer.json"
    cd vendor/krankikom/pimcore-skeleton
    mv composer.json composer.org
    cat composer.org | sed  "s/\"pimcore\/pimcore\": \"[^\"]*\"/\"pimcore\/pimcore\": \"$PIMCORE_VERSION_OVERRIDE\"/g" > composer.json
    echo "* Creating git repo"
    git init --initial-branch=master
    git add .
    git tag dummyskel
    git commit -m "Initial commit"
    echo "* Dummy skel done"
    cd $OPWD

    COMPOSER_EXTRA_PARAMS='--repository=/packages.json'
fi

echo "********************"
echo "********************"
echo "********************"

echo "**** Creating web using composer skeleton at $PWD"
docker run --rm -it -v ~/.ssh:/root/.ssh -v ~/.composer:/composer -v $SKELETON_PACKAGES_JSON:/packages.json -v $SKELETON_TMPDIR/vendor/krankikom/pimcore-skeleton:/skel --env-file $TMP_ENVFILE -v ${PWD}:/app --user $(id -u):$(id -g) composer-pdo -v --ignore-platform-reqs -n create-project -s dev krankikom/pimcore-skeleton "$NAME" "$PIMCORE_SKELETON_VERSION" --repository-url="$COMPOSER_REPO_URL" $COMPOSER_EXTRA_PARAMS


if [ ! -d "$NAME" ]; then
    echo "Skeleton creation seems to have failed :-("
    exit 2
fi

cd $NAME

# We do this because the composer images php version does not necessarily match the php version in the skeleton
echo "*** Wiping composer lockfile"
rm composer.lock

echo "**** Initializing git repo"
git init

#if [ "$GITPOD_REPO_ROOT" == "/workspace/pimcore-jetpakk" ]; then
#    echo "**** Adding local jetpakk composer repo"
#    docker run --rm -it -e COMPOSER_HOME=/app/composer-home --env-file $TMP_ENVFILE -v ~/.ssh:/root/.ssh  -v ${PWD}:/app --user $(id -u):$(id -g) composer-pdo config repositories.jetpakk path $GITPOD_REPO_ROOT
#    docker run --rm -it -e COMPOSER_HOME=/app/composer-home --env-file $TMP_ENVFILE -v ~/.ssh:/root/.ssh  -v ${PWD}:/app -v /workspace/pimcore-jetpakk:/workspace/pimcore-jetpakk  --user $(id -u):$(id -g) composer-pdo update -n --ignore-platform-reqs krankikom/pimcore-jetpakk @dev
#fi

echo "**** Adding default envrc"
vendor/krankikom/pimcore-jetpakk/devsetup/generate-envrc.sh -n "$NAME" -v "$VAGRANT_HOSTNAME" -p "$PIMCORE_IMAGE_LABEL"

echo "**** Creating dockerfiles/compose"
./docker-build.sh -o

echo "**** Adding default kubernetes configs"
mkdir kubernetes
cd kubernetes
ln -s ../vendor/krankikom/pimcore-jetpakk/kubernetes/$KUBERNETES_VERSION bases
cp -r ../vendor/krankikom/pimcore-jetpakk/kubernetes/example/. .
../vendor/krankikom/pimcore-jetpakk/kubernetes/randomize-secrets.sh
for ENV in dev staging production; do
  if [ ! -d "$ENV" ]; then
      continue
  fi
  cd ${ENV}
  mv php-fpm/ingress-${KUBERNETES_VERSION}.yaml php-fpm/ingress.yaml
  rm php-fpm/ingress-v*.yaml
  cd ..
done
cd ..


source .envrc

if [ ! -z $DOCKER_HOST ]; then

    which vagrant
    if [ $? -eq 0 ]; then
        echo "**** Bringing up vagrant box"
        vagrant up
        echo "**** Waiting 10sec for docker daemon to launch"
        sleep 10
    else
        echo "**** ERROR: Vagrant not installed, not starting anything"
        exit 9
    fi

fi

# the pimcore-init service has a dependency on db, however I've observed that docker-compose often doesn't actually bring it up
echo "**** Bringing up db"
$DOCKER_COMPOSE_CMD up -d db
sleep 10

echo "**** Running pimcore-init"
$DOCKER_COMPOSE_CMD up pimcore-init
