<?php
/**
 * JOSPIA 2026 - Connexion à la base de données
 *
 * - Sur WampServer/XAMPP/InfinityFree : modifiez simplement les 4 valeurs
 *   ci-dessous par défaut (getenv() renverra false, donc la valeur de
 *   repli après "?:" sera utilisée).
 * - Sur Render (Docker) : ne modifiez rien ici, définissez plutôt les
 *   variables d'environnement DB_HOST / DB_NAME / DB_USER / DB_PASS
 *   dans le tableau de bord Render (onglet "Environment"). C'est plus
 *   sûr : vos identifiants ne sont jamais écrits dans le code.
 */
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'jospia2026');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306'); // Aiven/Clever Cloud utilisent souvent un port non-standard
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: ''); // chemin vers le certificat CA fourni par l'hébergeur (si connexion chiffrée exigée)

// Nom / thème de l'événement (facile à changer chaque année)
define('EVENT_NAME', 'JOSPIA 2026');
define('EVENT_FULL', "Journées Spirituelles Islamiques d'Anyama 2026");

// Dates de l'édition, affichées sur le badge des séminaristes
define('EVENT_JOUR_DEBUT', '22');
define('EVENT_JOUR_FIN', '28');
define('EVENT_MOIS_ANNEE', 'DÉCEMBRE 2026');

// Lien de paiement Wave marchand
// Numéro Wave qui reçoit les paiements (chiffres, sans +225)
define('WAVE_NUMERO', getenv('WAVE_NUMERO') ?: '0767752772');
define('WAVE_PAYMENT_LINK', getenv('WAVE_PAYMENT_LINK') ?: 'https://pay.wave.com/m/M_ci_LhLv7A4lJDJ8/c/ci/');

// Frais de participation au séminaire
define('FRAIS_PARTICIPATION', 5100);

// Seuil d'âge (inclus) jusqu'auquel le DORTOIR attribué devient
// automatiquement "Pépinière" (ex. 9 = "9 ans et moins").
// Le sous-comité et la section choisis, eux, ne sont jamais modifiés.
define('AGE_PEPINIERE_SEUIL', 9);

/**
 * BASE_URL - détecté automatiquement, quel que soit le nom/emplacement du
 * dossier du site (racine du serveur, /jospia2026/, /monsite/, etc.).
 * C'est ce qui évite les liens et le CSS cassés sur WampServer/XAMPP
 * quand le site n'est pas à la racine de "www"/"htdocs".
 */
$racineProjet = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$racineServeur = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : false;
$baseDetectee = '';
if ($racineProjet && $racineServeur && strpos($racineProjet, $racineServeur) === 0) {
    $baseDetectee = substr($racineProjet, strlen($racineServeur));
}
define('BASE_URL', rtrim($baseDetectee, '/'));

try {
    $optionsPdo = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true,   // réutilise les connexions MySQL entre les requêtes (charge élevée)
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (DB_SSL_CA !== '' && file_exists(DB_SSL_CA)) {
        $optionsPdo[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
        // Certains hébergeurs (ex. Aiven) utilisent un certificat dont le nom
        // d'hôte ne correspond pas toujours exactement : on garde la
        // vérification stricte activée par défaut pour la sécurité, mais
        // elle peut être désactivée via DB_SSL_VERIFY=0 si besoin en dépannage.
        if (getenv('DB_SSL_VERIFY') === '0') {
            $optionsPdo[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
    }
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $optionsPdo
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données. Vérifiez config/db et que MySQL est démarré. (" . $e->getMessage() . ")");
}

// Auto-migration : vérifiée une seule fois par démarrage du serveur (drapeau en mémoire/disque),
// et non à chaque requête, pour ne pas ralentir le site.
$drapeauMigration = sys_get_temp_dir() . '/jospia_migration_ok';
if (!is_file($drapeauMigration)) {
    try {
        $nbTables = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('paiements','config_quiz')")->fetchColumn();
        $aCodeRecu = $nbTables === 2 ? (int)$pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND ((table_name = 'paiements' AND column_name = 'code_recu') OR (table_name = 'comptes' AND column_name = 'mdp_initial') OR (table_name = 'paiements' AND column_name = 'numero_wave'))")->fetchColumn() : 0;
        if ($nbTables < 2 || $aCodeRecu < 3) {
            $pdo->exec(file_get_contents(__DIR__ . '/../sql/migration_features.sql'));
        }
        @file_put_contents($drapeauMigration, '1');
    } catch (Throwable $e) {
        error_log('Auto-migration JOSPIA échouée : ' . $e->getMessage());
    }
}
