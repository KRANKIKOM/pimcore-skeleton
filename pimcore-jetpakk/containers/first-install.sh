#!/usr/bin/env bash
cd /var/www/html

umask 0002
/usr/games/cowsay "Checking install status"
INSTALLED=$(mysql -u pimcore -ppimcore -h db pimcore -e "SELECT id FROM assets LIMIT 1" --raw -s --skip-column-names)

if [ "$INSTALLED" != "1" ]; then
  /usr/games/cowsay "First Composer install (--no-scripts)"
  COMPOSER_MEMORY_LIMIT=-1 composer install --no-scripts
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Pimcore installer"
  vendor/bin/pimcore-install --admin-username=admin --admin-password=admin --mysql-username=pimcore --mysql-password=pimcore --mysql-database=pimcore --mysql-host-socket=db --no-interaction
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Second Composer install (full)"
  COMPOSER_MEMORY_LIMIT=-1 composer install
  echo ""
  echo "-------------------------------------------------------------------------"

  /var/www/html/vendor/krankikom/pimcore-jetpakk/containers/first-install-pimcore-bundles.sh
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Updating database schema"
  bin/console doctrine:schema:update --force
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Running Doctrine Migrations"
  ./bin/console doctrine:migrations:migrate --no-interaction
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Running npm install if required"
  test -f packages.json && npm install || true
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

else

  /usr/games/cowsay "Pimcore already installed, running composer install"
  COMPOSER_MEMORY_LIMIT=3G composer install
  echo ""
  echo "-------------------------------------------------------------------------"

  /var/www/html/vendor/krankikom/pimcore-jetpakk/containers/first-install-pimcore-bundles.sh
  echo "-------------------------------------------------------------------------"
  echo ""

  /usr/games/cowsay "Updating database schema"
  bin/console doctrine:schema:update --force
  echo ""
  echo "-------------------------------------------------------------------------"
  echo ""

fi

bash /var/www/html/vendor/krankikom/pimcore-jetpakk/containers/install-pimcore-test-files.sh

/usr/games/cowsay "Creating public/var"
[ -d public/var ] || mkdir public/var

/usr/games/cowsay "Amending permissions"
chgrp -R www-data var
chgrp -R www-data public/var
chmod -R g+rw var
chmod -R g+rw public/var
echo ""
echo "-------------------------------------------------------------------------"
echo ""
