ARG ASSETS_DIR=assets

<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
###
### START: PIMCORE ASSET INSTALL
###


FROM pimcore/pimcore:<%=$PIMCORE_IMAGE_LABEL%> AS build

WORKDIR /var/www/html

COPY . /var/www/html
#COPY --chown=www-data:www-data --from=build /var/www/html/vendor /var/www/html/vendor

RUN set -eux; \
    mkdir -p -m777 /var/www/.composer; \
    COMPOSER_AUTH="{\"http-basic\": {\"repo.packagist.com\": {\"username\": \"krankikom\", \"password\": \"c8a7ecca281bc7a6682a01fc5cb406a73aabf86ed5ffa81285049e650b41\"}}}" composer install --ignore-platform-reqs --no-scripts


ENV MYSQL_HOST=127.0.0.1
ENV MYSQL_PORT=3306
ENV MYSQL_USER=pimcore
ENV MYSQL_PASSWORD=pimcore
ENV MYSQL_DB=pimcore
ENV REDIS_HOST=dummy

RUN ./bin/console assets:install

RUN ln -s /var/www/html /app

###
### END: PIMCORE ASSET INSTALL
###


<% fi %>



<%+ ../php-fpm/Dockerfile.snippet2-frontend-build %>



###
### START: APACHE
###

FROM debian:bullseye-slim

RUN apt-get update && apt-get install -y apache2 libapache2-mod-fcgid libfcgi0ldbl

RUN /usr/sbin/a2enmod env headers expires proxy proxy_http proxy_fcgi rewrite ssl auth_basic authz_groupfile

RUN mkdir /etc/apache2/ssl

COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /var/www/html/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/fpm-status-loop.sh /fpm-status-loop.sh
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /var/www/html/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/apache/ssl/* /etc/apache2/ssl/
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /var/www/html/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/apache/apache-virtualhost.conf /etc/apache2/sites-available/000-default.conf
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /var/www/html/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/apache/apache2-foreground /apache2-foreground

<% if [ ! -z "$DOCKER_UID" ]; then %>
RUN groupdel dialout
RUN find /var/ -user 33 -exec chown <%=$DOCKER_UID%> {} \;
RUN find /var/ -group 33 -exec chgrp <%=$DOCKER_GID%> {} \;
RUN usermod -u <%=$DOCKER_UID%> www-data
RUN groupmod -g <%=$DOCKER_GID%> www-data
RUN chgrp -R www-data /run
RUN chmod -R g+rw /run
RUN mkdir -p /var/log/apache2; chown -R www-data:www-data /var/log/apache2
<% fi -%>

<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
COPY --chown=www-data:www-data --from=build /var/www/html/ /var/www/html
COPY --chown=www-data:www-data . /var/www/html


<% if [ "$FRONTEND_BUILD_FLAVOUR" = "sass" ]; then -%>
COPY --chown=www-data:www-data --from=sass /src/css /var/www/html/public/css
<% else %>
ARG ASSETS_DIR
COPY --chown=www-data:www-data --from=webpack /var/www/html/public/${ASSETS_DIR} /var/www/html/public/${ASSETS_DIR}
<% fi -%>

COPY --chown=www-data:www-data --from=build /var/www/html/public/bundles /var/www/html/public/bundles
<% fi -%>

WORKDIR /var/www/html

EXPOSE 80
CMD ["/apache2-foreground"]


###
### END: APACHE
###
