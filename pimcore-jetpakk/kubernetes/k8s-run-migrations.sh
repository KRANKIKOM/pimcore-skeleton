#!/usr/bin/env bash
echo "**** DEBUG INFO ****"

nslookup $MYSQL_HOST; 
echo "**** Dumping Exports **** ":
export

echo ""
echo ""

if [ -d dist/var ]; then
    echo "**** Syncing var files from dist/ ****"
    rsync -av --no-perms --omit-dir-times --no-owner --no-group dist/var/ var/
fi

echo "**** Installing bundles ****"
./vendor/krankikom/pimcore-jetpakk/containers/first-install-pimcore-bundles.sh

 if [ -z "$(find var/classes/DataObject/ -name '*.php' 2>/dev/null)" ] || [ "$PIMCORE_CLASS_DEFINITION_WRITABLE" = "1" ]; then
    echo "**** Running Classes-Rebuild ****"
    ./bin/console pimcore:deployment:classes-rebuild -cd --no-interaction
 else 
    echo "**** Skipping Classes Rebuild, PIMCORE_CLASS_DEFINITION_WRITABLE=$PIMCORE_CLASS_DEFINITION_WRITABLE ****"
 fi

echo "**** Running Migrations ****"
./bin/console doctrine:migrations:migrate --no-interaction
