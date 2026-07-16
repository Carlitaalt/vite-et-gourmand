#!/bin/bash
set -e

# Sécurité : re-nettoie les modules MPM en conflit à chaque démarrage
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf
rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf

# Railway fournit un port dynamique via $PORT ; on l'applique à Apache
: "${PORT:=80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground