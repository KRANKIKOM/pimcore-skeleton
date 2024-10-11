FROM node:17-bullseye


ADD vendor/krankikom/pimcore-jetpakk/containers/frontend-build-container/webpack/install_webpack_apt_requirements.sh /install_webpack_apt_requirements.sh 
RUN /install_webpack_apt_requirements.sh

# WEBPACK NPM REQUIREMENTS
ENV CPPFLAGS="-DPNG_ARM_NEON_OPT=0"
RUN mkdir /npm-tmp
COPY *.json /npm-tmp/
RUN cd /npm-tmp/; \
    if [ -e package.json ]; then \
        npm install --unsafe-perm=true --allow-root --include=dev; \
        if [ -e /npm-tmp/node_modules/optipng-bin/vendor/optipng ]; then rm /npm-tmp/node_modules/optipng-bin/vendor/optipng; ln -s /usr/bin/optipng /npm-tmp/node_modules/optipng-bin/vendor/optipng; fi; \
    	if [ -e /npm-tmp/node_modules/jpegtran-bin/vendor/jpegtran ]; then rm /npm-tmp/node_modules/jpegtran-bin/vendor/jpegtran; ln -s /usr/bin/jpegtran /npm-tmp/node_modules/jpegtran-bin/vendor/jpegtran; fi; \	 
    fi

# SASS
RUN mkdir /sass-build/
COPY vendor/krankikom/pimcore-jetpakk/containers/frontend-build-container/sass/* /sass-build/
RUN cd /sass-build/;npm install --unsafe-perm=true --allow-root


# GO!
COPY vendor/krankikom/pimcore-jetpakk/containers/frontend-build-container/start.sh /start.sh
WORKDIR /var/www/html
USER www-data
CMD /start.sh

