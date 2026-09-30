# ============================================================
# JOSPIA 2026 - Image Docker pour déploiement sur Render.com
# Render n'a pas de runtime PHP natif : ce Dockerfile fournit
# PHP 8.2 + Apache avec l'extension pdo_mysql nécessaire au site.
# ============================================================
FROM php:8.2-apache

# Extensions PHP nécessaires (connexion MySQL via PDO)
# GD (freetype/jpeg/webp) : génération des badges et diplômes PDF
RUN apt-get update && apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libwebp-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql mysqli opcache

# Active mod_rewrite (non strictement requis par le site, mais utile
# et sans risque si vous ajoutez des règles plus tard)
RUN a2enmod rewrite deflate expires headers

# Évite l'avertissement "Could not reliably determine the server's
# fully qualified domain name" dans les logs Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Réglages PHP : mise en tampon de la sortie (nécessaire pour les redirections
# et session_regenerate_id() après l'affichage de l'en-tête) et erreurs masquées
COPY docker/php-perf.ini /usr/local/etc/php/conf.d/jospia.ini
COPY docker/apache-perf.conf /etc/apache2/conf-available/perf.conf
RUN a2enconf perf && sed -i "s/AllowOverride None/AllowOverride All/" /etc/apache2/apache2.conf

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
