#!/bin/sh

# Run Laravel specific commands
php /var/www/liftr-main/artisan optimize
php /var/www/liftr-main/artisan config:cache
php /var/www/liftr-main/artisan route:cache
php /var/www/liftr-main/artisan config:clear
php /var/www/liftr-main/artisan route:clear
php /var/www/liftr-main/artisan cache:clear

# echo "Running migrations..."
# if ! php /var/www/liftr-main/artisan migrate --force; then
#   echo "Migration failed, stopping deployment."
#   exit 1
# fi

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in background
nginx

# Start Supervisor (which manages Horizon and Scheduler) in foreground to keep container alive
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
