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


#!/bin/sh
php-fpm -D &&  nginx -g "daemon off;"
