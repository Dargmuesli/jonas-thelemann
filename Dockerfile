# Base image (buster contains PHP >= 7.3, which is needed for "thesoftwarefanatics/php-html-parser")
FROM node:13.1.0-buster@sha256:0809b7a2b06d8673d4f24a0259e18178c76187924ca31cb70a1f460b8ac127fd AS stage_build

# Update and install PHP
RUN \
    apt-get update && \
    apt-get install -y composer git php php-dom php-mbstring unzip

# Import project files
COPY ./ /app/
WORKDIR /app/

# Install Gulp and build project
RUN yarn global add gulp-cli
RUN yarn add gulp@4 -D
RUN gulp build

# Base image
FROM php:7.3-fpm-alpine@sha256:bdd5fd3c39fdd9189f5e89fd83f83dfc9827ea1840a140ef2efbbc496405d692 AS stage_serve

# Environment variables
ENV PHP_INI_DIR /usr/local/etc/php
ENV PROJECT_NAME jonas-thelemann
# ENV PROJECT_MODS expires headers macro rewrite ssl

# Enable extensions
RUN apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install \
    pdo_pgsql

# Copy built source files, changing the server files' owner
COPY --chown=www-data:www-data --from=stage_build /app/dist/$PROJECT_NAME/ /var/www/$PROJECT_NAME/

# Copy PHP configuration files
COPY ./docker/php/php.ini $PHP_INI_DIR/
COPY --chown=www-data:www-data ./docker/php/prepend.php $PHP_INI_DIR/

# Declare required mount points
VOLUME /var/www/credentials/$PROJECT_NAME.env

# Update workdir to server files' location
WORKDIR /var/www/$PROJECT_NAME/
