FROM php:8.2

ARG USERNAME=app
ARG GROUPNAME=app
ARG UID=1000
ARG GID=1000
RUN groupadd -g $GID $GROUPNAME \
    && useradd -m -u $UID -g $GID $USERNAME

# Add to download Composer packages from dist.
RUN apt-get update && apt-get install -y unzip

# Add to install symfony/panther.
RUN apt-get update \
    && apt-get install -y \
        libzip-dev \
        zip \
    && docker-php-ext-install zip

# Add to measure code coverage.
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apt-get update \
    && apt-get install -y chromium-driver

COPY --from=composer /usr/bin/composer /usr/bin/composer
