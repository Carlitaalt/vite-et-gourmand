#!/bin/bash
set -e

# Railway fournit un port dynamique via $PORT ; on l'applique à Apache
: "${PORT:=80}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-enabled/000-default.conf

exec apache2-foreground