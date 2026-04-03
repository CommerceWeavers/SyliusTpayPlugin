ARG VERSION=php8.4

FROM debian:bookworm-slim AS wkhtmltopdf_build

ARG TARGETARCH

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl \
    && rm -rf /var/lib/apt/lists/* \
    && curl -fsSL "https://github.com/wkhtmltopdf/packaging/releases/download/0.12.6.1-3/wkhtmltox_0.12.6.1-3.bookworm_${TARGETARCH}.deb" \
       -o /tmp/wkhtmltox.deb \
    && dpkg-deb -x /tmp/wkhtmltox.deb /tmp/wkhtmltox

FROM dunglas/frankenphp:${VERSION} AS php_base

ARG EXTRA_EXTENSIONS=""

RUN install-php-extensions ast bcmath exif gd intl pdo pdo_mysql pdo_pgsql soap zip xdebug xsl ftp redis ${EXTRA_EXTENSIONS}

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_HOME=/.composer

RUN mkdir /.composer

ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=wkhtmltopdf_build /tmp/wkhtmltox/usr/local/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf
COPY --from=wkhtmltopdf_build /tmp/wkhtmltox/usr/local/bin/wkhtmltoimage /usr/local/bin/wkhtmltoimage

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       libxrender1 libxext6 libx11-6 libxcb1 fontconfig libjpeg62-turbo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

FROM php_base AS php_prod_build

COPY composer.json /app/
COPY tests/Application/src/Kernel.php /app/tests/Application/src/Kernel.php

RUN  composer install --no-dev --no-interaction --no-scripts; \
     composer require symfony/runtime sylius/refund-plugin --no-interaction

WORKDIR /app/tests/Application

FROM node:22-alpine AS node_build

WORKDIR /app

COPY --from=php_prod_build /app/vendor /app/vendor
COPY tests/Application/assets/ /app/tests/Application/assets/
COPY tests/Application/package.json tests/Application/webpack.config.js /app/tests/Application/

WORKDIR /app/tests/Application

RUN yarn install; \
    yarn build:prod

FROM php_base AS php_prod

ENV APP_ENV=prod

COPY .docker/php/prod/php.ini "$PHP_INI_DIR/"
COPY .docker/Caddyfile.prod /etc/frankenphp/Caddyfile
COPY .docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

COPY --from=node_build /app/tests/Application/public/build /app/tests/Application/public/build
COPY --from=php_prod_build /app/vendor /app/vendor

COPY . /app/

RUN composer dump-autoload --optimize

WORKDIR /app/tests/Application

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
