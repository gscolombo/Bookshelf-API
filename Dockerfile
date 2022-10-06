FROM php:8.0-apache
COPY ./rewrite.sh /
RUN a2enmod rewrite && service apache2 restart
RUN chmod +x /rewrite.sh && sed -i 's/\r$//' /rewrite.sh && /bin/bash /rewrite.sh
RUN apt-get update && apt-get install -y  libzip-dev zip \
&& docker-php-ext-install mysqli pdo pdo_mysql zip

# Composer installation
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY ./src/composer* $WORKDIR
COPY ./src/vendor $WORDIR
