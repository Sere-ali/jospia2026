# ============================================================
# JOSPIA 2026 — Image Docker pour déploiement sur Render.com
# Render n'a pas de runtime PHP natif : ce Dockerfile fournit
# PHP 8.2 + Apache avec l'extension pdo_mysql nécessaire au site.
# ============================================================
FROM php:8.2-apache

# Extensions PHP nécessaires (connexion MySQL via PDO)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Active mod_rewrite (non strictement requis par le site, mais utile
# et sans risque si vous ajoutez des règles plus tard)
RUN a2enmod rewrite

# Évite l'avertissement "Could not reliably determine the server's
# fully qualified domain name" dans les logs Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Réglages PHP : mise en tampon de la sortie (nécessaire pour les redirections
# et session_regenerate_id() après l'affichage de l'en-tête) et erreurs masquées
RUN printf "output_buffering=On\ndisplay_errors=Off\nlog_errors=On\n" > /usr/local/etc/php/conf.d/jospia.ini

# Copie du code source de l'application
COPY . /var/www/html/

# Le dossier uploads/ doit être accessible en écriture par Apache
# (photos des membres/séminaristes)
RUN chown -R www-data:www-data /var/www/html/uploads \
    && chmod -R 755 /var/www/html/uploads

# Render fournit dynamiquement le port d'écoute via la variable
# d'environnement $PORT (souvent 10000) : ce script adapte la
# configuration Apache à ce port avant de démarrer le serveur.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
