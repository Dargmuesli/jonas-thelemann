# Base image (buster contains PHP >= 7.3, which is needed for "thesoftwarefanatics/php-html-parser")
FROM node:13.4.0-buster@sha256:e3d6fded1691dd96fe9097071fce91a2d1ba5c675092cc04b81745e9a6b9ccdc AS stage_build

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
FROM php:7.4-fpm-alpine@sha256:98653dd3ecc849d3edba30fe3743feb8e1df087767e225b6dc0410574234184d AS development

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
