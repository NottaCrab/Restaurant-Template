#!/bin/bash
set -e

# Configurar Apache para escuchar en el puerto indicado por la variable $PORT
# que Railway asigna dinámicamente en tiempo de ejecución
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

exec "$@"
