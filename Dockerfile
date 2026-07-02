FROM php:8.1-fpm

#Use root user
USER root

#Copy composer file
COPY composer.lock composer.json /var/www/

#Change work directories
WORKDIR /var/www

# Install dependencies & extensions
RUN apt update && apt install -y \
        nodejs \
        npm \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        zip \
        curl \
        unzip \
        libsodium-dev \
        cron \
        exiftool \
        libmagickwand-dev \
        imagemagick \
          \
        supervisor \
    && docker-php-ext-install -j$(nproc) gd \
        pdo_mysql \
        mysqli \
        zip \
        pcntl \
        sodium \
        exif \
    && pecl install imagick \
        redis \
    && docker-php-ext-enable exif \
        imagick \
    && docker-php-source delete

RUN mkdir -p /usr/src/php/ext/redis \
       && curl -L https://github.com/phpredis/phpredis/archive/5.3.4.tar.gz | tar xvz -C /usr/src/php/ext/redis --strip 1 \
       && echo 'redis' >> /usr/src/php-available-exts \
       && docker-php-ext-install redis \
       && docker-php-ext-enable redis \
       && docker-php-ext-install soap \
       && docker-php-ext-enable soap \
       && docker-php-ext-configure gd --with-freetype --with-jpeg \
       && docker-php-ext-install -j$(nproc) gd

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN sed -i "s/max_execution_time = 30/max_execution_time = 60/g" /usr/local/etc/php/php.ini
RUN sed -i "s/post_max_size = 8M/post_max_size = 60M/g" /usr/local/etc/php/php.ini
RUN sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 40M/g" /usr/local/etc/php/php.ini
RUN sed -i "s/output_buffering = 4096/output_buffering = 4096/g" /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
#RUN composer install --ignore-platform-reqs

COPY ./ImageMagick-6/policy.xml /etc/ImageMagick-6/policy.xml

#Copy existing application directory contents
COPY . .