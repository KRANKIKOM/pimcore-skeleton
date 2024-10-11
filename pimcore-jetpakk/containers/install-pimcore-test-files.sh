#!/usr/bin/env bash

if [ ! -d "vendor/pimcore/pimcore/tests" ]; then

  CURRENT_PIMCORE_VERSION=$(composer show pimcore/pimcore | grep 'version' | grep -o -E '\*\ .+' | cut -d' ' -f2 | cut -d',' -f1);
  CURRENT_PIMCORE_HASH=$(composer show pimcore/pimcore | grep 'source' | grep -o -E '\git\ .+' | cut -d' ' -f2);

  echo "Installing pimcore test data for version $CURRENT_PIMCORE_VERSION ($CURRENT_PIMCORE_HASH)"

  git clone --depth 1 --filter=blob:none --no-checkout https://github.com/pimcore/pimcore
  cd pimcore
  git config --local gc.auto 0 #fixes weird issue git unpacking objects forever. newer git on mac behaves differently
  git checkout $CURRENT_PIMCORE_HASH -- tests
  cd ../
  mv pimcore/tests vendor/pimcore/pimcore
  rm -rf pimcore
  
fi
