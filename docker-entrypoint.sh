#!/bin/sh
# Render impose son propre port d'écoute via la variable $PORT
# (Apache écoute sur le port 80 par défaut, il faut l'adapter).
set -e

PORT="${PORT:-10000}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
