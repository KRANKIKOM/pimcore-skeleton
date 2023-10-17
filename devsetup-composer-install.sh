#!/usr/bin/env bash

echo "**** Creating custom composer docker image"

COMPOSER_REPO_URL="https://repo.packagist.com/krankikom/"
COMPOSER_AUTH_TOKEN="c8a7ecca281bc7a6682a01fc5cb406a73aabf86ed5ffa81285049e650b41"
COMPOSER_AUTH_ENVVAR="{\"http-basic\": {\"repo.packagist.com\": {\"username\": \"krankikom\", \"password\": \"$COMPOSER_AUTH_TOKEN\"}}}"

DOCKERFILE_TMPDIR=`mktemp -d`
DOCKERFILE="$DOCKERFILE_TMPDIR/Dockerfile"
cat >$DOCKERFILE <<EOT
FROM composer
RUN docker-php-ext-install mysqli pdo pdo_mysql
EOT

docker build -t composer-pdo $DOCKERFILE_TMPDIR

echo "**** Running composer install"
TMP_ENVFILE=$(mktemp)

cat <<EOT >> $TMP_ENVFILE
COMPOSER_AUTH=$COMPOSER_AUTH_ENVVAR
EOT

docker run --rm -i --env-file $TMP_ENVFILE -v "${PWD}:/app" -v ~/.ssh:/root/.ssh -v ~/.composer:/composer --user $(id -u):$(id -g) composer-pdo install -v --ignore-platform-reqs --no-scripts