FROM php:8.2-apache

# Extensions PHP nécessaires (PDO MySQL + MongoDB)
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git \
    pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Supprime en dur TOUS les modules MPM, puis ne réactive que prefork
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -sf ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -sf ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite

# Désactive l'affichage public des erreurs PHP (sécurité en production)
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "error_log = /var/log/apache2/php_errors.log" >> /usr/local/etc/php/conf.d/errors.ini

# Augmente les limites d'upload PHP (photos de menus)
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_file_uploads = 10" >> /usr/local/etc/php/conf.d/uploads.ini

# Désactive l'affichage public des erreurs PHP (sécurité)
RUN echo "display_errors = Off" >> /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du code
COPY . /var/www/html/

# Installe les dépendances PHP (phpmailer etc.)
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Droits d'écriture si besoin (uploads d'images de menus par ex.)
RUN chown -R www-data:www-data /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

CMD ["/usr/local/bin/docker-entrypoint.sh"]