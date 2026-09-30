# JOSPIA 2026 - Plateforme de gestion complète

Site professionnel (HTML/CSS/JS + PHP/MySQL) pour la gestion des **Journées Spirituelles Islamiques d'Anyama**.
Identité graphique **Vert & Blanc**, conforme au cahier des charges et à la maquette validée.
✅ Testé de bout en bout sur un vrai serveur MySQL (installation, inscriptions avec upload de photo, connexion, test d'entrée noté, affectation automatique de dortoir/niveau, téléchargement badge/diplôme, contrôle d'accès).

## 1. Fonctionnalités incluses

- **Inscription des membres de commission** (nom, commission parmi les 13 proposées, contact, photo) → **badge généré automatiquement**, visible uniquement sur la page personnelle du membre, **téléchargeable/imprimable uniquement par Admin/Super Admin**.
- **Inscription des séminaristes** : identité, genre, niveau d'études, Anyama 1/2 + sections dynamiques (LYMA, SAINT MICHEL, ATLAS, LYMAO, YVAC, GAOUSSOU, LA PERRUCHE / GSAMAT, BUTHMAAN, SOUNTIATA KEÏTA), lieu de résidence, maladie (paludisme, asthme, allergie, autre à préciser), âge, contact, photo, **contact d'urgence parent/tuteur**.
- **Affectation automatique du dortoir** : 4 dortoirs Hommes + 4 dortoirs Femmes (répartition équilibrée), plus un dortoir **Pépinière** dédié pour les séminaristes de `AGE_PEPINIERE_SEUIL` ans (9 par défaut) et moins. **Le sous-comité (Anyama) et la section choisis ne sont jamais modifiés automatiquement**, quel que soit l'âge - seul le dortoir en dépend.
- **Matricule séquentiel des séminaristes** : format `JOS-001`, `JOS-002`, etc. (incrémental automatique).
- **Section "Autre"** disponible dans la liste déroulante des sections, avec champ de précision.
- **Badge automatique pour les séminaristes** (en plus des membres de commission), reprenant fidèlement la maquette officielle AEEMCI/JOSPIA (logos, calligraphie, photo circulaire, dortoir/niveau/matricule, dates de l'édition) : visible en aperçu sur leur espace personnel, téléchargement réservé aux admins. Dates de l'édition modifiables via `EVENT_JOUR_DEBUT`/`EVENT_JOUR_FIN`/`EVENT_MOIS_ANNEE` dans `config/db`.
- **Badge officiel également pour les membres de commission** (thème vert & or, maquette AEEMCI/JOSPIA fournie), avec le même mécanisme (aperçu verrouillé + téléchargement/impression en lot réservés aux admins).
- **Pépinière = dortoir ET niveau** : les séminaristes du dortoir Pépinière (≤ `AGE_PEPINIERE_SEUIL` ans) sont automatiquement affectés au niveau "Pépinière" et **dispensés du test d'entrée** (accès au quiz bloqué, message explicite sur leur espace). Le filtre d'impression des badges par niveau inclut Pépinière.
- **Note du test d'entrée sur le bulletin** : en plus des notes de composition saisies par la commission scientifique, le bulletin affiche la note et le niveau obtenus au test d'entrée en ligne.
- **Boutons "Modifier"** sur toutes les listes admin (membres de commission, séminaristes, comptes admin), avec recalcul automatique et sûr du dortoir/niveau si l'âge ou le genre d'un séminariste est corrigé après coup.
- **Critiques des séminaristes envers les commissions** : chaque séminariste peut donner son avis (texte libre) sur une commission depuis son espace personnel. Le message est transmis directement aux membres de cette commission (visible sur leur propre espace, sans révéler l'auteur), et une vue d'ensemble est disponible pour les admins (**💬 Critiques**, avec le nom de l'auteur pour la supervision).
- **Impression des bulletins en lot** (2 par page imprimée), filtrable par niveau (y compris Pépinière).
- **Impression des badges en lot** (4 par page imprimée) pour les membres de commission et les séminaristes, avec filtre par commission/dortoir.
- **Listes par dortoir et par niveau** (admin) : pages listant nommément les séminaristes groupés par dortoir (y compris Pépinière) et par niveau d'affectation, imprimables.
- **Fiche d'inscription imprimable** par le séminariste lui-même, avec le nom du dortoir attribué.
- **Test d'entrée en ligne noté /20**, 20 questions (Coran, Fiqh, Aqida, culture générale islamique et académique, gestion associative AEEMCI), minuteur de 20 minutes, correction et **affectation de niveau 100% automatiques** :
  - Note < 5 → **Primaire**
  - 5 à 8,99 → **Secondaire**
  - 9 à 12,99 → **Universitaire**
  - 13 à 20 → **Leader**
- **Diplôme généré automatiquement** après le test, visible sur la page du séminariste, téléchargeable uniquement par Admin/Super Admin.
- **Bulletin de notes des compositions du séminaire** : la commission scientifique (comptes Admin/Super Admin) saisit une note par matière pour chaque séminariste. Le bulletin est **généré automatiquement** sur la page du séminariste, mais reste **invisible tant que le Super Admin n'a pas publié les résultats** (bouton dédié). Les matières et leur barème sont personnalisables.
- **Correction détaillée du test d'entrée** : le séminariste (et les admins) peuvent consulter, question par question, sa réponse et la bonne réponse, pour justifier la note obtenue.
- **3 espaces** : Utilisateur (membre/séminariste), Admin, Super Admin.
- Identifiants de connexion **générés automatiquement** à l'inscription.
- Design **vert & blanc** professionnel, entièrement **responsive**.
- Sécurité : mots de passe hashés (bcrypt), contrôle d'accès par rôle, dossiers techniques protégés.

> **Vous avez déjà installé une version précédente avec de vraies inscriptions ?** N'écrasez pas votre base : importez uniquement `sql/migration_bulletin_notes.sql` (ajoute les nouvelles tables sans toucher aux données existantes). Si votre base ne contient que des données de test, réimportez simplement `sql/jospia2026_complet.sql`.

## 2. Installation - Méthode recommandée (base de données déjà prête)

1. Copiez le dossier `jospia2026` dans `htdocs` (XAMPP/WAMP) ou à la racine de votre hébergement.
2. Dans **phpMyAdmin** → onglet **Importer** → sélectionnez le fichier `sql/jospia2026_complet.sql` → **Exécuter**.
   → Crée automatiquement la base `jospia2026` avec les 6 tables, les 8 dortoirs, les 20 questions du test et le compte Super Admin.
   (En ligne de commande : `mysql -u root -p < sql/jospia2026_complet.sql`)
3. Ouvrez `config/db` et renseignez vos identifiants MySQL (`DB_HOST`, `DB_USER`, `DB_PASS`).
4. Rendez-vous sur `http://localhost/jospia2026/login` et connectez-vous :
   - Identifiant : `superadmin`
   - Mot de passe : `jospia2026`
   → puis changez immédiatement ce mot de passe via **"Mon compte"**.
5. Supprimez le fichier `install.php` (non nécessaire avec cette méthode).

## 2bis. Méthode alternative (base vide + script d'installation)

1. Créez une base MySQL **vide** nommée `jospia2026`.
2. Renseignez `config/db`.
3. Ouvrez `http://localhost/jospia2026/install` (crée les tables + seed automatiquement).
4. Supprimez `install.php` une fois terminé.

## 2ter. Hébergement sur InfinityFree (gratuit)

InfinityFree ne permet **pas** de créer/supprimer une base de données par script (droits limités), et impose un **nom de base et d'utilisateur préfixés** (ex. `epiz_12345678_jospia2026`). Utilisez donc le fichier dédié `sql/jospia2026_infinityfree.sql` (sans instructions `CREATE DATABASE`/`DROP DATABASE`/`USE`), pas `jospia2026_complet.sql`.

**Étapes :**
1. Créez un compte sur [infinityfree.net](https://infinityfree.net) et un nouvel hébergement (sous-domaine gratuit type `votresite.infinityfreeapp.com`, ou domaine perso).
2. Dans le **Panel de contrôle** → **MySQL Databases** : créez une base (ex. `jospia2026`). InfinityFree génère automatiquement le nom complet (ex. `epiz_12345678_jospia2026`), le nom d'utilisateur (même préfixe) et vous demande un mot de passe. **Notez ces 3 informations + le nom d'hôte MySQL affiché (ex. `sqlXXX.infinityfree.com`).**
3. Ouvrez **phpMyAdmin** depuis le panel → sélectionnez votre base → onglet **Importer** → choisissez `sql/jospia2026_infinityfree.sql` → **Exécuter**.
4. Modifiez `config/db` avec les informations exactes fournies par InfinityFree :
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');   // hôte MySQL donné par InfinityFree
   define('DB_NAME', 'epiz_12345678_jospia2026');  // nom EXACT de votre base
   define('DB_USER', 'epiz_12345678');              // utilisateur EXACT fourni
   define('DB_PASS', 'votre_mot_de_passe_mysql');
   ```
5. Uploadez tout le contenu du dossier `jospia2026` (pas le dossier lui-même, son **contenu**) dans `htdocs/` via le **Gestionnaire de fichiers** du panel ou en **FTP** (FileZilla - identifiants FTP fournis par InfinityFree).
6. Dans le panel, section **PHP Version** (parfois sous "Software") : sélectionnez **PHP 8.0 ou supérieur**.
7. Vérifiez que les dossiers `uploads/photos` et `uploads/badges` restent accessibles en écriture (droits 755 par défaut, suffisant généralement).
8. Rendez-vous sur votre domaine InfinityFree, connectez-vous avec `superadmin` / `jospia2026`, changez le mot de passe, puis supprimez `install.php` (inutile ici puisque vous avez importé le `.sql` directement).

**Limites à connaître sur le plan gratuit :** taille de base limitée (~400 Mo, largement suffisant), pas de tâche planifiée (cron), et un délai possible avant l'activation du certificat SSL gratuit sur les sous-domaines.

## 2quater. Hébergement sur Render.com (via Docker) + base MySQL Aiven (gratuite)

Render ne propose pas de runtime PHP natif (uniquement Node, Python, Ruby, Go, Rust, Elixir ou **Docker**) et n'offre pas de base MySQL gérée (seulement PostgreSQL). Ce projet est donc livré avec un `Dockerfile` prêt à l'emploi, et vous connecterez une base MySQL gratuite hébergée chez **Aiven** (1 Go gratuit à vie, sans carte bancaire).

### Étape A - Créer la base MySQL sur Aiven
1. Créez un compte sur [aiven.io](https://aiven.io) → nouveau service → **MySQL** → plan **Free**.
2. Une fois le service démarré, allez dans l'onglet **Overview** : notez **Host**, **Port**, **User** (souvent `avnadmin`), **Password**, et le nom de la base (`defaultdb` par défaut, ou créez-en une nommée `jospia2026`).
3. Téléchargez le certificat **CA Certificate** proposé par Aiven (fichier `ca.pem`) - la connexion est chiffrée par défaut.
4. Ouvrez la base via un client MySQL (MySQL Workbench, TablePlus, ou en ligne de commande avec le certificat) et importez **`sql/jospia2026_infinityfree.sql`** (même fichier "sans CREATE/DROP DATABASE" que pour InfinityFree - adapté à toute base déjà existante).

### Étape B - Déployer le site sur Render
1. Poussez ce dossier `jospia2026` dans un dépôt GitHub (Render déploie depuis Git).
2. Sur [render.com](https://render.com) → **New** → **Web Service** → connectez votre dépôt → Render détecte automatiquement le `Dockerfile` (Environment = **Docker**). Le fichier `render.yaml` inclus permet aussi un déploiement en un clic via **New → Blueprint**.
3. Plan **Free**.
4. Onglet **Environment** → ajoutez ces variables (valeurs obtenues à l'étape A) :
   ```
   DB_HOST = votre-service.aivencloud.com
   DB_PORT = 12345          (port fourni par Aiven, pas 3306)
   DB_NAME = jospia2026
   DB_USER = avnadmin
   DB_PASS = votre_mot_de_passe_aiven
   ```
5. **Certificat SSL (recommandé par Aiven)** : uploadez `ca.pem` dans le dépôt (ex. dans `config/aiven-ca.pem`) et ajoutez la variable :
   ```
   DB_SSL_CA = /var/www/html/config/aiven-ca.pem
   ```
   Sans cette variable, la connexion tentera de se faire sans certificat - Aiven l'autorise généralement quand même, mais le mode chiffré est plus sûr.
6. Cliquez sur **Create Web Service**. Render construit l'image Docker et démarre le site (URL type `https://jospia2026.onrender.com`).
7. Connectez-vous avec `superadmin` / `jospia2026`, changez le mot de passe immédiatement.

### Notes importantes pour Render
- Le plan **Free** de Render met le service en veille après 15 minutes d'inactivité (le premier chargement après une pause peut prendre ~30-50 secondes) - normal, pas un bug.
- Les photos uploadées (`uploads/photos/`) sont stockées **dans le conteneur** : sur le plan gratuit (sans disque persistant), elles disparaissent à chaque redéploiement. Pour un usage réel avec beaucoup d'inscriptions, prévoyez soit un disque persistant Render (payant), soit un service de stockage externe (non inclus dans cette version).
- `install.php` est volontairement exclu de l'image Docker (`.dockerignore`) : utilisez uniquement l'import SQL via Aiven (étape A).


## 3. Structure du projet

```
jospia2026/
├── Dockerfile / docker-entrypoint.sh / render.yaml / .dockerignore  # déploiement Render (Docker)
├── index.php / inscription_commission.php / inscription_seminariste.php
├── quiz.php / login.php / logout.php / compte.php / install.php
├── config/db            # connexion MySQL + réglages (seuil pépinière, etc.) - lit aussi les variables d'environnement
├── includes/                # fonctions métier, authentification, header/footer
├── espace/fiche         # espace personnel (badge en aperçu / fiche+diplôme)
├── admin/                   # tableau de bord, listes, téléchargements, CRUD questions, comptes
├── assets/css/style.css     # design vert & blanc, responsive
├── assets/js/main.js        # sections dynamiques, minuteur, aperçu photo
├── uploads/photos/          # photos des membres/séminaristes
└── sql/
    ├── jospia2026_complet.sql       # ⭐ base complète prête à importer (localhost/Wamp/XAMPP)
    ├── jospia2026_infinityfree.sql  # ⭐ même contenu, adapté à l'hébergement mutualisé (InfinityFree)
    ├── jospia.sql                   # schéma seul
    └── questions_seed.php           # banque de 20 questions
```

## 4. Rôles et permissions

| Rôle | Droits |
|---|---|
| Membre de commission | Voir sa fiche/badge en aperçu (téléchargement non autorisé) |
| Séminariste | Voir sa fiche, **l'imprimer lui-même**, composer le test, voir son diplôme en aperçu (téléchargement non autorisé) |
| Admin | Voir/filtrer toutes les inscriptions, télécharger badges/fiches/diplômes, tableau de bord |
| Super Admin | Tout ce qu'un Admin peut faire + gérer les comptes admin + gérer la banque de questions |

## 5. Notes importantes

- Le seuil d'âge "Pépinière" est actuellement `<= 9 ans` (9 ans et moins, voir `config/db`, constante `AGE_PEPINIERE_SEUIL`).
- Aucune librairie externe (Composer/PDF) n'est requise : badges, fiches et diplômes s'impriment/s'enregistrent en PDF via la fonction **Imprimer** du navigateur (Ctrl+P → "Enregistrer en PDF").
- Compatible PHP 8+ et MySQL/MariaDB.
