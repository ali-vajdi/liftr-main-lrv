# Use official PHP image as base
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/liftr-main

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copy the Laravel project files to the container
COPY . /var/www/liftr-main

# Install project dependencies
RUN composer install --prefer-dist --no-scripts --no-dev --no-progress --no-interaction

# Set permissions for Laravel storage and cache directories
RUN chown -R www-data:www-data /var/www/liftr-main/storage /var/www/liftr-main/bootstrap/cache \
    && chmod -R 775 /var/www/liftr-main/storage /var/www/liftr-main/bootstrap/cache

# Ensure the www-data user can write to the storage/logs directory
RUN chown -R www-data:www-data /var/www/liftr-main/storage /var/www/liftr-main/bootstrap/cache
RUN chmod -R 775 /var/www/liftr-main/storage /var/www/liftr-main/bootstrap/cache

# Install Nginx
RUN apt-get install -y nginx && rm -rf /var/lib/apt/lists/*

# Copy Nginx configuration
COPY ./dockerfiles/production/.nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./dockerfiles/production/.nginx/vhost.conf /etc/nginx/conf.d/default.conf

# Copy entrypoint script
COPY ./dockerfiles/production/.docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose port 80
EXPOSE 80

# Use entrypoint script to start both services
CMD ["/entrypoint.sh"]
