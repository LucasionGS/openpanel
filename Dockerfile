FROM ubuntu:22.04

# Install php 8.1 and some extensions
RUN apt update
RUN apt install -y software-properties-common python3-launchpadlib
RUN add-apt-repository ppa:ondrej/php
RUN apt update
RUN apt install -y \
    nginx \
    php8.1 \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-curl \
    php8.1-gd \
    php8.1-intl \
    php8.1-mbstring \
    php8.1-soap \
    php8.1-xml \
    php8.1-xmlrpc \
    php8.1-zip

WORKDIR /var/www/html
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

RUN mkdir /var/www/vhosts -p
# Make sure www-data owns all files PHP should write to
RUN chown www-data:www-data /var/www -R
RUN chown www-data:www-data /etc/nginx/sites-available -R
RUN chown www-data:www-data /etc/nginx/sites-enabled -R
RUN chmod 775 /var/www -R

CMD ["bash", "/start.sh"]