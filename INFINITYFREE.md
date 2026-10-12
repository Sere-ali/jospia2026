# Mise en ligne sur InfinityFree (sans carte bancaire)

1. **Compte et site** : créez un compte sur infinityfree.com, puis un site (sous-domaine gratuit).
2. **Base MySQL** : panneau > « MySQL Databases » > créez une base. Notez : MySQL Hostname, DB Name, User Name.
3. **Import** : « phpMyAdmin » > ouvrez votre base > « Importer » > choisissez `sql/jospia2026_infinityfree.sql`. Les tables manquantes (paiements, quiz…) se créent toutes seules à la première visite.
4. **Configuration** : copiez `config/db.local.php.example` en `config/db.local.php` et remplissez vos valeurs.
5. **Fichiers** : gestionnaire de fichiers (ou FileZilla) > dossier `htdocs` > envoyez TOUT le contenu du zip (pas le dossier lui-même). `index.php` doit être directement dans `htdocs`.
6. **Test** : ouvrez votre adresse. Compte initial : identifiant `superadmin`. **Changez son mot de passe dès la première connexion** (page « Mon compte »).
7. **Supprimez** `install.php` du serveur une fois le site en marche.

Limites : mode de paiement MANUEL uniquement (pas d'API Wave). Gardez `db.local.php` secret.
