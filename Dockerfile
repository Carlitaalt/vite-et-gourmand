FROM php:8.2-apache

# Extensions PHP nécessaires (PDO MySQL + MongoDB)
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git \
    pkg-config libssl-dev \
    && docker-php-ext-install pdo pdo_mysql mysqli \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Active mod_rewrite (utile si tu ajoutes des routes propres plus tard)
RUN a2enmod rewrite

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du code
COPY . /var/www/html/

# Installe les dépendances PHP (phpmailer etc.)
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Droits d'écriture si besoin (uploads d'images de menus par ex.)
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80