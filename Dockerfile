FROM php:8.3-fpm

# Set user
ARG user=admin
ARG uid=1000

# Install system dependencies
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

# Install and enable xdebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Configure xdebug
RUN echo "xdebug.mode=develop,debug,coverage" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Copy custom configurations PHP
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Copy all files
COPY . .

# Install dependencies
RUN composer install

USER $user