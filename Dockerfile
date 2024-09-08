FROM ubuntu:24.04

COPY .env /var/www/.env

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get upgrade -y; apt-get install -y \
    apache2 php8.3 php8.3-mysql libapache2-mod-php8.3 \
    php-mysql php-gd curl

# php and apache settings
RUN <<EOF
  mkdir /var/log/pon4
  chown www-data:www-data /var/log/pon4

  mkdir /var/www/tmp
  chown www-data:www-data /var/www/tmp

  mkdir /var/www/html/gifs
  chown www-data:www-data /var/www/html/gifs

  # increase the max filesize for POST requests to 16MB
  sed -i 's/upload_max_filesize.*/upload_max_filesize = 16M/' /etc/php/8.3/apache2/php.ini
  sed -i 's/post_max_size.*/post_max_size = 16M/' /etc/php/8.3/apache2/php.ini

  # enable error logging
  sed -i 's@log_errors.*@log_errors = On@' /etc/php/8.3/apache2/php.ini
  sed -i 's@error_log.*@error_log = /dev/stderr@' /etc/php/8.3/apache2/php.ini

  # enable apache modules
  a2enmod php8.3
  a2enmod rewrite

  # enable .htaccess files in web dirs
  sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

  service apache2 restart
EOF

# stock command to run continuously so the docker container doesn't exit.
CMD ["apachectl", "-D", "FOREGROUND"]
