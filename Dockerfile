# Base image (buster contains PHP >= 7.3, which is needed for "thesoftwarefanatics/php-html-parser")
FROM php:7.4.21-cli-buster@sha256:0123a175be90b0b2de60631ba23e601c7f97abf0ce85586257635dde0e505f29 AS build

# Update and install build dependencies
# Git is required for gulp's sitemap sitemap.
RUN \
    apt-get update \
    && apt-get install --no-install-recommends -y git nodejs npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Import project files
COPY ./ /srv/app/
WORKDIR /srv/app/

# Install Gulp and build project
RUN npm install -g yarn
RUN yarn global add gulp-cli
RUN yarn add gulp@4 -D
RUN yarn build


# Base image (buster contains python2, which is needed for node-sass)
FROM php:7.4.21-fpm-buster@sha256:064b9ef4bfffed9f50796940f97e9c71f3a930ee1526a0ab1d2f4f0b98cd349e AS development

# Environment variables
ENV PHP_INI_DIR /usr/local/etc/php
ENV PROJECT_NAME jonas-thelemann

# Enable extensions
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install \
    pdo_pgsql

# Copy PHP configuration files
COPY --chown=www-data:www-data ./docker/php/* $PHP_INI_DIR/

# Declare required mount points
VOLUME /var/www/$PROJECT_NAME/credentials/$PROJECT_NAME.env

# Update workdir to server files' location
WORKDIR /var/www/$PROJECT_NAME/


# Base image
FROM php:7.4-fpm-alpine@sha256:1da273dd89ff75d55e8c248a7154860bfee5e39f31af7b362054c890e498797f AS production

# Environment variables
ENV PHP_INI_DIR /usr/local/etc/php
ENV PROJECT_NAME jonas-thelemann

# Enable extensions
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install \
    pdo_pgsql

# Copy built source files, changing the server files' owner
COPY --chown=www-data:www-data --from=build /srv/app/dist/$PROJECT_NAME/ /usr/src/$PROJECT_NAME/

# Copy PHP configuration files
COPY --chown=www-data:www-data ./docker/php/* $PHP_INI_DIR/

# Copy the entrypoint script to root
COPY ./docker/entrypoint.sh /

# Declare required mount points
VOLUME /var/www/$PROJECT_NAME/credentials/$PROJECT_NAME.env

# Update workdir to server files' location
WORKDIR /var/www/$PROJECT_NAME/

# Specify entrypoint script, that updates the source files in the shared volume (nginx)
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]