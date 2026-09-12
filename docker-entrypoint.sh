#!/bin/bash
set -e

# Sécurité : re-nettoie les modules MPM en conflit à chaque démarrage
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

# En local, le code est monté dans le conteneur (volume) : si les dépendances Composer
# ne sont pas encore installées sur la machine, on les installe au premier démarrage
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html
fi

# Railway fournit un port dynamique via $PORT ; on l'applique à Apache
: "${PORT:=80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground