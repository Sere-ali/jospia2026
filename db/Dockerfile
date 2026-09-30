# MariaDB pour JOSPIA 2026 (service privé Render, disque persistant sur /var/lib/mysql)
FROM mariadb:10.11
# Importé automatiquement au tout premier démarrage (datadir vide)
COPY sql/databasejospia26.sql /docker-entrypoint-initdb.d/01-jospia2026.sql
# datadir dans un sous-dossier : évite le "lost+found" à la racine du disque Render
CMD ["mariadbd", "--datadir=/var/lib/mysql/data", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]
