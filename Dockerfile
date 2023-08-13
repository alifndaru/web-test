FROM php:8.1-fpm

ARG user=alifndaru
ARG uid=1000

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets
RUN apt-get update && \
    apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install dom

RUN docker-php-ext-install pdo_mysql



# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ADD . /var/www/
RUN chmod -R 777 /var/www/
# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# COPY php.ini /usr/local/etc/php/php.ini

USER $user