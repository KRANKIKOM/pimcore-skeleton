#!/bin/bash
if [ "$FRONTEND_BUILD_FLAVOUR" == "sass" ]; then
    echo "*********** SASS MODE ************"

    node /sass-build/build.js
else
    echo "*********** WEBPACK MODE ************"

    echo "*** Copying NPM Modules"
    cp -r /npm-tmp/node_modules .

    echo "*** Running Webpack"
    node node_modules/.bin/webpack --watch --config node_modules/kk-webpack-base--config
fi
