#!/bin/sh
set -e

IS_PHP_FPM_LAUNCH=0
if [ "$@" = "php-fpm" ]; then
	IS_PHP_FPM_LAUNCH=1
fi

#echo "ENTRYPOINT: Setting umask"
umask 0002


if [ -n "$REDIS_HOSTPORT" ]; then
	#echo "ENTRYPOINT: Splitting REDIS_HOSTPORT=$REDIS_HOSTPORT into host/port"
	export REDIS_HOST=${REDIS_HOSTPORT%%:*}
	export REDIS_PORT=${REDIS_HOSTPORT##*:}
	#echo "ENTRYPOINT: REDIST_HOST=$REDIS_HOST REDIS_PORT=$REDIS_PORT"
fi

if [ -n "$MYSQL_HOSTPORT" ]; then
	#echo "ENTRYPOINT: Splitting MYSQL_HOSTPORT=$MYSQL_HOSTPORT into host/port"
	export MYSQL_HOST=${MYSQL_HOSTPORT%%:*}
	export MYSQL_PORT=${MYSQL_HOSTPORT##*:}
	#echo "ENTRYPOINT: MYSQL_HOST=$MYSQL_HOST MYSQL_PORT=$MYSQL_PORT"
fi

if [ -z $MYSQL_PORT ]; then
	export MYSQL_PORT="3306"
fi

if [ "$RUN_DISTVAR_SYNC" = "1" ]; then
	echo "ENTRYPOINT: Syncing dist/var/ to var/"
	rsync -av --no-perms --omit-dir-times --no-owner --no-group dist/var/ var/
fi

if [ ! -z $MYSQL_HOST ] && [ "$IS_PHP_FPM_LAUNCH" = "1" ]; then
	echo "ENTRYPOINT: Clearing cache"
	./bin/console cache:clear --no-interaction -e ${PIMCORE_ENVIRONMENT}

# takes to much time for orthomol with a lot of documents, assets and objects
# side-note: cache is in redis so this should be run in a seperate job and
#            not block a container start/deployment
#	echo "ENTRYPOINT: Warming cache"
#	./bin/console pimcore:cache:warming --no-interaction -e ${PIMCORE_ENVIRONMENT}

fi

# Lets run the assets hard copy only in kubernetes and not in local setups
# ("composer install" will take care of the symlinks in local setups)
if [ "$IS_PHP_FPM_LAUNCH" = "1" ] &&  [ ! -z "$KUBERNETES_SERVICE_HOST" ] && [ "$SKIP_ASSETS_INSTALL" != "1" ]; then
  echo "ENTRYPOINT: copy assets"
  ./bin/console assets:install
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	echo "ENTRYPOINT: Appending apache foreground parameter"
	set -- php-fpm "$@"
fi

# echo "ENTRYPOINT: Tailing logfile"
# ls -al /var/log/fpm-php.www.log
# tail -f /var/log/fpm-php.www.log &

# echo "ENTRYPOINT: Tailing access logfile"
# ls -al /var/log/fpm-php.access.log
# tail -f /var/log/fpm-php.access.log &


if [ "$RUN_PIMCORE_INSTALL" = "1" ]; then
	if [ ! -f "./var/config/system.yml" ]; then
		echo "ENTRYPOINT: Running pimcore install"
		vendor/bin/pimcore-install --admin-username=admin --admin-password=admin --mysql-username="$MYSQL_USER" --mysql-password="$MYSQL_PASSWORD" --mysql-database="$MYSQL_DB" --mysql-host-socket="$MYSQL_HOST" --no-interaction
	else
		echo "ENTRYPOINT: Skipping pimcore install, ./var/config/system.yml already exists"
		ls -al var/config/system.yml
	fi
fi

if [ "$RUN_CLASSES_REBUILD" = "1" ]; then
	echo "ENTRYPOINT: Running classes-rebuild"
	./bin/console pimcore:deployment:classes-rebuild -cd --no-interaction -e ${PIMCORE_ENVIRONMENT}
fi


if [ "$RUN_MIGRATIONS" = "1" ]; then
	echo "ENTRYPOINT: Running migrations"
	./bin/console doctrine:migrations:migrate --no-interaction
fi


if [ -n "$PRE_LAUNCH_COMMAND" ]; then
	echo "ENTRYPOINT: Running PRE_LAUNCH_COMMAND: $PRE_LAUNCH_COMMAND"
	$PRE_LAUNCH_COMMAND
fi

if [ "$SLEEP_INFINITY" = "1" ]; then
	echo "ENTRYPOINT: Sleeping forever"
	sleep infinity
fi




CACHEDIR=${PIMCORE_SYMFONY_CACHE_DIRECTORY:-/var/www/html/var/cache}
if [ -n "$PIMCORE_ENVIRONMENT" ] && [ "$SKIP_PRELOADER" != "1" ]; then
	CACHEDIR_FOR_ENV=${CACHEDIR}/${PIMCORE_ENVIRONMENT}
	if [ -d "$CACHEDIR_FOR_ENV" ]; then
		echo "ENTRYPOINT: Found symfony cache directory for environment $PIMCORE_ENVIRONMENT: $CACHEDIR_FOR_ENV"

		DEV_PRELOAD="$CACHEDIR_FOR_ENV/App_KernelDevContainer.preload.php"
		PROD_PRELOAD="$CACHEDIR_FOR_ENV/App_KernelProdContainer.preload.php"

		if [ "$PIMCORE_ENVIRONMENT" = "dev" ] && [ -f "$DEV_PRELOAD" ]; then
			echo "ENTRYPOINT: Found preload file for $PIMCORE_ENVIRONMENT environment: $DEV_PRELOAD "
			PRELOAD="$DEV_PRELOAD"
		fi
		if [ "$PIMCORE_ENVIRONMENT" = "prod" ] && [ -f "$PROD_PRELOAD" ]; then
			echo "ENTRYPOINT: Found preload file for $PIMCORE_ENVIRONMENT environment: $PROD_PRELOAD "
			PRELOAD="$PROD_PRELOAD"
		fi
		if [ -n "$PRELOAD" ]; then
			echo "ENTRYPOINT: Configuring opcache.preload=$PRELOAD"
			echo "opcache.preload=$PRELOAD" > /usr/local/etc/php/conf.d/preload.ini
			echo "opcache.preload_user=www-data" >> /usr/local/etc/php/conf.d/preload.ini
		fi
	fi
	
fi

if [ "$IS_PHP_FPM_LAUNCH" = "1" ]; then
	echo "ENTRYPOINT: Starting fpm status loop"
	/fpm-status-loop.sh &
fi


echo "ENTRYPOINT: Launching $@"
exec "$@"
