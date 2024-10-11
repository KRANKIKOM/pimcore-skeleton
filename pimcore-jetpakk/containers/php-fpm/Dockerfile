# Global scope build args need to be referenced in build stages empty
# Reference: https://github.com/moby/moby/issues/34482#issuecomment-454716952
ARG ASSETS_DIR=<%=$ASSETS_DIR%>

<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
<%+ Dockerfile.snippet1-composer %>
<%+ Dockerfile.snippet2-frontend-build %>
<% fi %>

###
### START: Application Layer
###

FROM ghcr.io/krankikom/pimcore-docker-base:<%=$PIMCORE_IMAGE_LABEL%>

ENV REDIS_HOST=redis
ENV REDIS_PORT=6379

USER root

<% if [ ! -z "$DOCKER_UID" ]; then %>
RUN find /var/log -user 33 -exec chown <%= $DOCKER_UID %> {} \;
RUN find /var/log -group 33 -exec chgrp <%= $DOCKER_GID %> {} \;
RUN if getent group <%= $DOCKER_GID %>; then groupdel $(getent group <%= $DOCKER_GID %> | cut -d: -f1); fi
RUN groupmod -g <%= $DOCKER_GID %> www-data
RUN usermod -u <%= $DOCKER_UID %> www-data
RUN usermod -g <%=$DOCKER_GID %> www-data
<% fi -%>


# PROJECT-SPECIFIC EXTRA REQUIREMENTS
RUN mkdir /tmp/setup/
# We're using "setup*/*" because this handles cases where the project-specific setup folder simply doesn't exist
# This hack is needed because there is no "conditional copy"/"copy if file exists" function in Dockerfiles
COPY setup*/* /tmp/setup/
RUN if [ -f /tmp/setup/docker-php-image-extra-requirements.sh ]; then bash /tmp/setup/docker-php-image-extra-requirements.sh; fi

<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
<%+ Dockerfile.snippet3a-application-copy %>
<%+ Dockerfile.snippet3b-frontend-build-copy %>
<% fi %>


# Create php logfile
RUN touch /var/log/fpm-php.www.log && chown www-data:www-data /var/log/fpm-php.www.log && touch /var/log/fpm-php.access.log && chown www-data:www-data /var/log/fpm-php.access.log

# PHP SETTINGS
<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
RUN echo 'error_reporting=E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE' > /usr/local/etc/php/conf.d/error_reporting.ini
<% else %>
RUN echo 'error_reporting=E_ALL' > /usr/local/etc/php/conf.d/error_reporting.ini
<% fi %>
RUN echo 'pm.status_path = /status' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.status_listen = 0.0.0.0:9001' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen = 0.0.0.0:9000' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'php_flag[display_errors] = on' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'php_admin_value[error_log] = /var/log/fpm-php.www.log' >> /usr/local/etc/php-fpm.d/www.conf \    
    && echo 'access.log = /var/log/fpm-php.access.log' >> /usr/local/etc/php-fpm.d/www.conf \    
    && echo 'php_admin_flag[log_errors] = on' >> /usr/local/etc/php-fpm.d/www.conf \    
    && echo 'catch_workers_output = yes' >> /usr/local/etc/php-fpm.d/www.conf

    
# PHP LIMITS
RUN touch /usr/local/etc/php/conf.d/limits.ini \
    && echo "upload_max_filesize = 256M;" >> /usr/local/etc/php/conf.d/limits.ini \
    && echo "post_max_size = 350M;" >> /usr/local/etc/php/conf.d/limits.ini \
    && echo "memory_limit = 512M;" >> /usr/local/etc/php/conf.d/limits.ini \
    && echo "max_execution_time = 300;" >> /usr/local/etc/php/conf.d/limits.ini

RUN touch /usr/local/etc/php/conf.d/preload.ini && chown www-data /usr/local/etc/php/conf.d/preload.ini

<% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %>
<%+ Dockerfile.snippet4-application-scripts %>
<% fi %>



USER www-data
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /app/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/fpm-status-loop.sh /fpm-status-loop.sh
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /app/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/php-fpm/entrypoint.sh /entrypoint.sh
COPY <% if [ $DOCKER_COMPOSE_BUILD = "false" ]; then %> --from=build /app/<% fi %>vendor/krankikom/pimcore-jetpakk/containers/php-fpm/php-fpm-healthcheck /php-fpm-healthcheck

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

###
### END: Application Layer
###
