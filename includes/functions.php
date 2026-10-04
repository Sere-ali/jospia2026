<?php
/**
 * JOSPIA 2026 - Fonctions utilitaires
 */

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function genererMatricule($prefixe) {
    return $prefixe . '-' . date('y') . '-' . strtoupper(substr(uniqid(), -6));
}

/** Matricule séquentiel des séminaristes : JOS-001, JOS-002, ... */
function genererMatriculeSeminariste(PDO $pdo) {
    $stmt = $pdo->query("SELECT matricule FROM seminaristes WHERE matricule REGEXP '^JOS-[0-9]+$' ORDER BY CAST(SUBSTRING(matricule, 5) AS UNSIGNED) DESC LIMIT 1");
    $dernier = $stmt->fetchColumn();
    $prochain = 1;
    if ($dernier) {
        $prochain = ((int) substr($dernier, 4)) + 1;
    }
    return 'JOS-' . str_pad((string)$prochain, 3, '0', STR_PAD_LEFT);
}

function genererIdentifiantMotDePasse($contact) {
    $mdp = (string) random_int(100000, 999999);
    return [$contact, $mdp];
}

/** Upload sécurisé d'une photo, retourne le nom de fichier stocké ou null */
function uploadPhoto($fichier, $sousDossier = 'photos') {
    if (!isset($fichier) || $fichier['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensionsAutorisees)) {
        return null;
    }
    if ($fichier['size'] > 5 * 1024 * 1024) { // 5 Mo max
        return null;
    }
    $nomFichier = uniqid('photo_', true) . '.' . $ext;
    $cheminDestination = __DIR__ . '/../uploads/' . $sousDossier . '/' . $nomFichier;
    if (move_uploaded_file($fichier['tmp_name'], $cheminDestination)) {
        // Copie durable en base de données : le disque du site est effacé à chaque mise à jour
        photoSauvegarderEnBase($nomFichier, $cheminDestination, $ext);
        return $nomFichier;
    }
    return null;
}

/**
 * Affecte automatiquement le dortoir :
 * - Jusqu'à AGE_PEPINIERE_SEUIL inclus (par défaut 9 ans, donc âge <= 9),
 *   le séminariste est logé dans "Pépinière" (dortoir dédié, sans limite
 *   de places suivie).
 * - Au-delà de ce seuil, répartition équilibrée entre les 4 dortoirs
 *   du genre concerné (celui qui a le moins d'occupants).
 * Le SOUS-COMITÉ (Anyama) et la SECTION choisis ne sont JAMAIS modifiés
 * automatiquement : ils restent toujours ceux choisis à l'inscription.
 */
function affecterDortoir(PDO $pdo, $genre, $age) {
    if ($age <= AGE_PEPINIERE_SEUIL) {
        return 'Pépinière';
    }
    $stmt = $pdo->prepare("SELECT id, nom, capacite, occupation FROM dortoirs WHERE genre = ? ORDER BY occupation ASC, id ASC LIMIT 1");
    $stmt->execute([$genre]);
    $dortoir = $stmt->fetch();
    if (!$dortoir) {
        return 'Non affecté';
    }
    $upd = $pdo->prepare("UPDATE dortoirs SET occupation = occupation + 1 WHERE id = ?");
    $upd->execute([$dortoir['id']]);
    return $dortoir['nom'];
}

/** Liste des sections par Anyama */
if (!defined('ANYAMA_EXTERIEUR')) define('ANYAMA_EXTERIEUR', 'Autre (extérieur)');
function sectionsParAnyama() {
    return [
        'Anyama 1' => ['LYMA', 'SAINT MICHEL', 'ATLAS', 'LYMAO', 'YVAC', 'GAOUSSOU', 'LA PERRUCHE', 'Autre'],
        'Anyama 2' => ['GSAMAT', 'BUTHMAAN', 'SOUNDJATA KEÏTA', 'Autre'],
        ANYAMA_EXTERIEUR => [], // personnes venant de l'extérieur : section facultative
    ];
}

/** La colonne seminaristes.anyama était un ENUM (Anyama 1 / 2) : elle devient un texte pour accepter « Autre (extérieur) ». */
function anyamaPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    try {
        $col = $pdo->query("SHOW COLUMNS FROM seminaristes LIKE 'anyama'")->fetch();
        $type = (string)($col['Type'] ?? $col['type'] ?? '');
        if ($type !== '' && stripos($type, 'enum') !== false) {
            $pdo->exec("ALTER TABLE seminaristes MODIFY anyama VARCHAR(40) NOT NULL");
        }
    } catch (Throwable $e) { error_log('Migration anyama : ' . $e->getMessage()); }
}

function listeCommissions() {
    $defaut = ['MG', 'MGA', 'ADMINISTRATION', 'SCIENTIFIQUE', 'MIC', 'FINANCE', 'SANTÉ', 'SÉCURITÉ', 'HYGIÈNE', 'PÉPINIÈRE', 'RESTAURATION', 'LOGISTIQUE', 'PROTOCOLE'];
    $enBase = trim((string)parametre('liste_commissions', ''));
    if ($enBase === '') return $defaut;
    $liste = array_values(array_filter(array_map('trim', preg_split('/\R/u', $enBase)), 'strlen'));
    return $liste ?: $defaut;
}

/**
 * Noms complets des commissions (badges et certificats). Modifiables par le super administrateur
 * dans « Paramètres du site » (une ligne par commission : SIGLE = Nom complet).
 */
function nomsCommissionsComplets() {
    static $map = null;
    if ($map !== null) return $map;
    $map = [
        'LOGISTIQUE' => 'Logistique et Transport',
        'HYGIÈNE' => 'Hygiène et Cadre de vie',
    ];
    foreach (preg_split('/\R/u', (string)parametre('noms_commissions', '')) as $ligne) {
        if (strpos($ligne, '=') === false) continue;
        [$court, $long] = array_map('trim', explode('=', $ligne, 2));
        if ($court !== '' && $long !== '') { $map[mb_strtoupper($court, 'UTF-8')] = $long; }
    }
    return $map;
}

function nomCommissionComplet($commission) {
    $c = trim((string)$commission);
    $map = nomsCommissionsComplets();
    return $map[mb_strtoupper($c, 'UTF-8')] ?? $c;
}

/** Réglage des niveaux (noms + notes de passage), modifiable par la commission scientifique. Défaut : Primaire <5, Secondaire <9, Universitaire <13, Leader. */
function reglageNiveaux($relire = false) {
    static $c = null;
    if ($c !== null && !$relire) return $c;
    $c = ['seuils' => [5.0, 9.0, 13.0], 'noms' => ['Primaire', 'Secondaire', 'Universitaire', 'Leader']];
    try {
        global $pdo;
        $lire = function ($cle) use ($pdo) {
            $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = ?");
            $st->execute([$cle]);
            return json_decode((string)$st->fetchColumn(), true);
        };
        $se = $lire('seuils_niveaux'); $no = $lire('noms_niveaux');
        if (is_array($se) && is_array($no) && count($no) >= 2 && count($no) <= 12 && count($se) === count($no) - 1
            && count(array_filter($no, 'strlen')) === count($no)) {
            $ok = $se[0] > 0 && end($se) <= 20;
            for ($i = 1; $i < count($se); $i++) { if (!($se[$i - 1] < $se[$i])) $ok = false; }
            if ($ok) { $c = ['seuils' => array_map('floatval', $se), 'noms' => array_map('strval', $no)]; }
        }
    } catch (Throwable $e) {}
    return $c;
}
function seuilsNiveaux() { return reglageNiveaux()['seuils']; }
function nomsNiveaux() { return reglageNiveaux()['noms']; }
/** Liste ordonnée des niveaux pour les filtres : Pépinière + les niveaux définis. */
function listeNiveaux() { return array_merge(['Pépinière'], nomsNiveaux()); }

/** Niveau correspondant à une note /20 selon des noms et seuils donnés */
function niveauPourNote($note, array $noms, array $seuils) {
    foreach ($seuils as $i => $seuil) { if ($note < $seuil) return $noms[$i]; }
    return end($noms);
}

/** Calcule le niveau d'affectation à partir de la note /20 (noms et notes réglables) */
function determinerNiveauTest($note) {
    return niveauPourNote($note, nomsNiveaux(), seuilsNiveaux());
}

/** Indique si le Super Admin / Admin a publié les résultats (bulletins visibles par les séminaristes) */
function resultatsPublies(PDO $pdo) {
    $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'resultats_publies'");
    $stmt->execute();
    return $stmt->fetchColumn() === '1';
}

/** Test d'entrée ouvert ? Verrouillé par défaut : la commission scientifique ou le super admin le déverrouille. */
function testOuvert(PDO $pdo) {
    try {
        $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'test_ouvert'");
        $st->execute();
        return $st->fetchColumn() === '1';
    } catch (Throwable $e) { return false; }
}

/** Correction détaillée du test visible par les séminaristes ? Verrouillée par défaut. */
function correctionOuverte(PDO $pdo) {
    try {
        $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'correction_ouverte'");
        $st->execute();
        return $st->fetchColumn() === '1';
    } catch (Throwable $e) { return false; }
}

/** La commission scientifique a-t-elle accès au test d'entrée ? (autorisé par le super administrateur ; le super admin y a toujours accès) */
function accesTestScientifique(PDO $pdo) {
    return true; // plus de verrouillage par le super admin : la commission scientifique gère le test
    if (estSuperAdmin()) return true;
    try {
        $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'test_acces_scientifique'");
        $st->execute();
        return $st->fetchColumn() === '1';
    } catch (Throwable $e) { return false; }
}

/** Bloque (message) la commission scientifique tant que le super administrateur n'a pas autorisé le test d'entrée. */
function exigerAccesTest(PDO $pdo) {
    if (accesTestScientifique($pdo)) return;
    $titrePage = "Test d'entrée verrouillé";
    require_once __DIR__ . '/header.php';
    require_once __DIR__ . '/admin_nav.php';
    echo '<section class="section"><div class="container"><div class="carte" style="max-width:640px;margin:0 auto;text-align:center;"><h3>🔒 Test d\'entrée verrouillé</h3><p>Le test d\'entrée (questions, configuration, déverrouillage) n\'est pas encore accessible à la commission scientifique. Il sera déverrouillé par le <strong>super administrateur</strong>.</p><a href="' . BASE_URL . '/admin/commission_scientifique" class="btn btn-outline btn-sm">&larr; Retour</a></div></div></section>';
    require_once __DIR__ . '/footer.php';
    exit;
}

function blocFormParametre($cle, $valeurActuelle, $libelleActiver, $libelleDesactiver, $classeActiver = 'btn-primaire') {
    $retour = strpos($_SERVER['PHP_SELF'], 'dashboard') !== false ? 'dashboard' : (strpos($_SERVER['PHP_SELF'], 'commission_scientifique') !== false ? 'scientifique' : '');
    return '<form method="post" action="' . BASE_URL . '/admin/test_entree" style="display:inline;"><input type="hidden" name="retour" value="' . $retour . '"><input type="hidden" name="cle" value="' . $cle . '"><input type="hidden" name="valeur" value="' . ($valeurActuelle ? '0' : '1') . '">'
        . '<button type="submit" class="btn btn-sm ' . ($valeurActuelle ? 'btn-danger' : $classeActiver) . '">' . ($valeurActuelle ? $libelleDesactiver : $libelleActiver) . '</button></form>';
}

/** Blocs de verrouillage du test d'entrée (super admin : accès commission scientifique + test des séminaristes). */
function blocTestEntree(PDO $pdo) {
    $h = '';
    if (accesTestScientifique($pdo)) {
        $ouvert = testOuvert($pdo);
        $h .= '<div class="carte" style="border-left:4px solid ' . ($ouvert ? 'var(--couleur-succes)' : '#dc3545') . ';margin-bottom:24px;">';
        $h .= '<h3>' . ($ouvert ? '🔓 Test ouvert aux séminaristes' : '🔒 Test fermé aux séminaristes') . '</h3>';
        $h .= '<p>' . ($ouvert ? 'Les séminaristes dont le paiement est validé peuvent composer le test.' : 'Les séminaristes (paiement validé) ne peuvent pas encore composer le test.') . '</p>';
        $h .= blocFormParametre('test_ouvert', $ouvert, '🔓 Ouvrir le test aux séminaristes', '🔒 Fermer le test') . '</div>';
        if (estScientifique()) {
            $co = correctionOuverte($pdo);
            $h .= '<div class="carte" style="border-left:4px solid ' . ($co ? 'var(--couleur-succes)' : '#dc3545') . ';margin-bottom:24px;">';
            $h .= '<h3>' . ($co ? '🔓 Correction du test visible par les séminaristes' : '🔒 Correction du test verrouillée') . '</h3>';
            $h .= '<p>' . ($co ? 'Les séminaristes ayant composé le test peuvent voir la correction détaillée de leurs réponses.' : 'Les séminaristes ne peuvent pas voir la correction détaillée de leur test.') . '</p>';
            $h .= blocFormParametre('correction_ouverte', $co, '🔓 Déverrouiller la correction', '🔒 Verrouiller la correction') . '</div>';
        }
    }
    return $h;
}

/** Appréciation littérale d'une note ramenée sur 20 (utilisée par le bulletin) */
function appreciationNote($ratio) {
    if ($ratio >= 16) return ['Excellent', 'vert'];
    if ($ratio >= 14) return ['Très bien', 'vert'];
    if ($ratio >= 12) return ['Bien', 'vert'];
    if ($ratio >= 10) return ['Passable', 'or'];
    return ['Insuffisant', 'rouge'];
}

/**
 * Recalcule le dortoir (et le niveau si Pépinière) d'un séminariste existant
 * après modification de son âge/genre, en ajustant proprement les compteurs
 * d'occupation des dortoirs (pas de double comptage).
 */
function recalculerDortoirSeminariste(PDO $pdo, array $ancien, $nouvelAge, $nouveauGenre) {
    $ancienDortoir = $ancien['dortoir'];
    $etaitPepiniere = ($ancienDortoir === 'Pépinière');
    $doitEtrePepiniere = ($nouvelAge <= AGE_PEPINIERE_SEUIL);

    // Toujours Pépinière : rien à changer sur le dortoir
    if ($etaitPepiniere && $doitEtrePepiniere) {
        $niveauAffecte = $ancien['test_complete'] ? $ancien['niveau_affecte'] : 'Pépinière';
        return ['dortoir' => 'Pépinière', 'niveau_affecte' => $niveauAffecte];
    }

    // Toujours un dortoir normal, même genre : on ne reshuffle pas inutilement
    if (!$etaitPepiniere && !$doitEtrePepiniere && $ancien['genre'] === $nouveauGenre && $ancienDortoir) {
        return ['dortoir' => $ancienDortoir, 'niveau_affecte' => $ancien['niveau_affecte']];
    }

    // Transition : on libère l'ancien dortoir suivi (s'il y en avait un, hors Pépinière)
    if (!$etaitPepiniere && $ancienDortoir) {
        $pdo->prepare("UPDATE dortoirs SET occupation = GREATEST(occupation - 1, 0) WHERE nom = ?")->execute([$ancienDortoir]);
    }

    if ($doitEtrePepiniere) {
        $niveauAffecte = $ancien['test_complete'] ? $ancien['niveau_affecte'] : 'Pépinière';
        return ['dortoir' => 'Pépinière', 'niveau_affecte' => $niveauAffecte];
    }

    $nouveauDortoir = affecterDortoir($pdo, $nouveauGenre, $nouvelAge);
    // Si le séminariste sort de Pépinière sans avoir composé de test, on retire le niveau "Pépinière"
    $niveauAffecte = ($ancien['niveau_affecte'] === 'Pépinière' && !$ancien['test_complete']) ? null : $ancien['niveau_affecte'];
    return ['dortoir' => $nouveauDortoir, 'niveau_affecte' => $niveauAffecte];
}

function redirect($url) {
    if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
        $url = BASE_URL . $url;
    }
    header("Location: $url");
    exit;
}

/** Valide un paiement et génère le code secret du reçu (utilisé dans le QR code). */
function validerPaiement(PDO $pdo, int $paiementId, ?int $validateurId): bool {
    $code = bin2hex(random_bytes(12));
    $st = $pdo->prepare("UPDATE paiements SET statut = 'validé', admin_validateur_id = ?, code_recu = COALESCE(code_recu, ?), date_validation = NOW(), montant = ? WHERE id = ? AND statut <> 'validé'");
    $st->execute([$validateurId, $code, FRAIS_PARTICIPATION, $paiementId]);
    return $st->rowCount() > 0;
}

/** Numéro Wave affiché : +225 05 46 15 53 98 */
function numeroWaveAffiche() {
    return '+225 ' . trim(chunk_split(WAVE_NUMERO, 2, ' '));
}

/** Lien de paiement Wave (marchand) avec le montant, ou null s'il n'est pas configuré. */
function lienWavePaiement() {
    if (strpos(WAVE_PAYMENT_LINK, 'VOTRE_MARCHAND_ID') !== false) return null;
    $base = preg_replace('/[?&]amount=\d+/', '', WAVE_PAYMENT_LINK);
    return $base . (strpos($base, '?') === false ? '?' : '&') . 'amount=' . (int)FRAIS_PARTICIPATION;
}

/** Répartit un nom en 1 ou 2 lignes équilibrées (majuscules) pour le badge. */
function josLignesNom($nom) {
    $mots = preg_split('/\s+/u', trim(mb_strtoupper($nom, 'UTF-8')), -1, PREG_SPLIT_NO_EMPTY);
    if (count($mots) <= 1) { return $mots ?: ['']; }
    $meilleur = null; $score = PHP_INT_MAX;
    for ($i = 1; $i < count($mots); $i++) {
        $a = implode(' ', array_slice($mots, 0, $i));
        $b = implode(' ', array_slice($mots, $i));
        $s = max(mb_strlen($a), mb_strlen($b));
        // à longueur égale on préfère la première ligne la plus longue (comme la maquette : « CHEICK OMER / DIARRA »)
        if ($s < $score || ($s === $score && mb_strlen($a) >= mb_strlen($b))) { $score = $s; $meilleur = [$a, $b]; }
    }
    return $meilleur;
}

/** Numéro de téléphone local : chiffres uniquement, sans indicatif 225 (ex : +225 05 46 15 53 98 => 0546155398). */
function numeroLocal($tel) {
    $n = preg_replace('/\D+/', '', (string)$tel);
    if (strpos($n, '00225') === 0) { $n = substr($n, 5); }
    elseif (strpos($n, '225') === 0 && strlen($n) >= 12) { $n = substr($n, 3); }
    return $n;
}

function synchroniserIdentifiantContact(PDO $pdo, $colonne, $id, $ancienContact, $nouveauContact) {
    if ($ancienContact === $nouveauContact || !in_array($colonne, ['membre_id', 'seminariste_id'], true)) { return null; }
    $st = $pdo->prepare("SELECT id, identifiant FROM comptes WHERE $colonne = ? LIMIT 1");
    $st->execute([$id]);
    $c = $st->fetch();
    if (!$c || $c['identifiant'] !== $ancienContact) { return null; }
    $st = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ? AND id <> ?");
    $st->execute([$nouveauContact, $c['id']]);
    if ($st->fetchColumn() > 0) { return null; }
    $pdo->prepare("UPDATE comptes SET identifiant = ? WHERE id = ?")->execute([$nouveauContact, $c['id']]);
    return $nouveauContact;
}

/** Table de stockage durable des photos (le disque du conteneur est éphémère sur Render). */
function photoTableBase(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS photos_stockees (
        nom VARCHAR(120) NOT NULL PRIMARY KEY,
        mime VARCHAR(40) NOT NULL,
        donnees MEDIUMBLOB NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function photoSauvegarderEnBase($nom, $chemin, $ext) {
    global $pdo;
    try {
        photoTableBase($pdo);
        $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $st = $pdo->prepare("INSERT INTO photos_stockees (nom, mime, donnees) VALUES (?,?,?) ON DUPLICATE KEY UPDATE mime = VALUES(mime), donnees = VALUES(donnees)");
        $st->bindValue(1, $nom);
        $st->bindValue(2, $mimes[$ext] ?? 'image/jpeg');
        $st->bindValue(3, file_get_contents($chemin), PDO::PARAM_LOB);
        $st->execute();
    } catch (Throwable $e) {
        error_log('Sauvegarde photo en base échouée : ' . $e->getMessage());
    }
}

/** Paiement automatique Wave (API Checkout) : actif seulement si la clé WAVE_API_KEY est définie sur Render. */
function waveApiActive() { return (bool)getenv('WAVE_API_KEY'); }

function waveApi($methode, $chemin, $corps = null) {
    $ch = curl_init('https://api.wave.com' . $chemin);
    $entetes = ['Authorization: Bearer ' . getenv('WAVE_API_KEY'), 'Content-Type: application/json'];
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_HTTPHEADER => $entetes, CURLOPT_CUSTOMREQUEST => $methode]);
    if ($corps !== null) { curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($corps)); }
    $rep = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $rep ? json_decode($rep, true) : null];
}

/** Adresse publique du site (https) pour les retours Wave. */
function urlSite() {
    $hote = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return 'https://' . $hote . BASE_URL;
}

/**
 * Interroge Wave sur une session de paiement ; si le paiement a abouti, enregistre l'ID de transaction
 * et valide automatiquement. Retourne true si le paiement est confirmé.
 */
function waveVerifierEtValider(PDO $pdo, $sessionId) {
    [$code, $j] = waveApi('GET', '/v1/checkout/sessions/' . rawurlencode($sessionId));
    if ($code !== 200 || !is_array($j) || ($j['payment_status'] ?? '') !== 'succeeded') { return false; }
    if ((int)($j['amount'] ?? 0) < FRAIS_PARTICIPATION) { return false; }
    $st = $pdo->prepare("SELECT id FROM paiements WHERE wave_session_id = ? LIMIT 1");
    $st->execute([$sessionId]);
    $pid = (int)$st->fetchColumn();
    if (!$pid) {
        // Inscription en attente de paiement : le dossier n'existe pas encore, on le crée maintenant que Wave confirme.
        try {
            inscriptionsAttentePreparer($pdo);
            $sa = $pdo->prepare("SELECT * FROM inscriptions_attente WHERE wave_session_id = ? LIMIT 1");
            $sa->execute([$sessionId]);
            if ($att = $sa->fetch()) {
                return finaliserInscriptionAttente($pdo, $att, $sessionId, (string)($j['transaction_id'] ?? '')) !== null;
            }
        } catch (Throwable $e) { error_log('Inscription en attente : ' . $e->getMessage()); }
        return false;
    }
    $tx = (string)($j['transaction_id'] ?? '');
    $pdo->prepare("UPDATE paiements SET reference_transaction = ? WHERE id = ?")->execute([$tx !== '' ? $tx : 'WAVE-' . $sessionId, $pid]);
    validerPaiement($pdo, $pid, null);
    return true;
}

/* ------------------------------------------------------------------ */
/*  Paramètres du site + journal d'activité                            */
/* ------------------------------------------------------------------ */

/** Valeur d'un paramètre du site (table parametres), ou $defaut. */
function parametre($cle, $defaut = null) {
    global $JOS_PARAMS;
    return $JOS_PARAMS[$cle] ?? $defaut;
}

function journalPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    $drapeau = sys_get_temp_dir() . '/jospia_journal_ok';
    if (is_file($drapeau)) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS journal_activite (
        id INT AUTO_INCREMENT PRIMARY KEY, compte_id INT NULL, nom VARCHAR(150) NOT NULL DEFAULT '', role VARCHAR(20) NOT NULL DEFAULT '',
        action VARCHAR(150) NOT NULL, cible VARCHAR(200) NOT NULL DEFAULT '', ip VARCHAR(45) NOT NULL DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_journal_date (created_at), INDEX idx_journal_compte (compte_id)) ENGINE=InnoDB");
    @file_put_contents($drapeau, '1');
}

/** Enregistre une action dans le journal (ne provoque jamais d'erreur visible). */
function journaliser(PDO $pdo, $action, $cible = '', $compte = null) {
    try {
        journalPreparer($pdo);
        $c = $compte ?: ($_SESSION['compte'] ?? null);
        $pdo->prepare("INSERT INTO journal_activite (compte_id, nom, role, action, cible, ip) VALUES (?,?,?,?,?,?)")
            ->execute([$c['id'] ?? null, (string)($c['nom_affiche'] ?? ($c['identifiant'] ?? '')), (string)($c['role'] ?? ''), mb_substr((string)$action, 0, 150), mb_substr((string)$cible, 0, 200), substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45)]);
    } catch (Throwable $e) { error_log('Journal : ' . $e->getMessage()); }
}

/**
 * Journal automatique : toute action de modification faite par un compte du personnel
 * (admin, super admin, finance, scientifique) est enregistrée : qui, quoi, sur qui.
 */
function journalAutomatique(PDO $pdo) {
    $u = $_SESSION['compte'] ?? null;
    if (!$u || !in_array($u['role'], ['admin', 'superadmin', 'finance', 'scientifique', 'securite'], true)) return;
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '', '.php');
    if (in_array($script, ['login', 'logout', 'wave_webhook', 'wave_retour', 'wave_pay'], true)) return;
    $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $action = null;
    if ($methode === 'POST') {
        $action = $_POST['action'] ?? ($_POST['cle'] ?? ($_POST['ouvrir'] ?? ($_POST['publier'] ?? 'enregistrer')));
    } else {
        foreach (['supprimer' => 'supprimer', 'publier' => 'publier', 'reset' => 'reset', 'activer' => 'activer', 'desactiver' => 'désactiver'] as $k => $a) {
            if (isset($_GET[$k])) { $action = $a; break; }
        }
        if ($script === 'pdf') { $action = 'télécharger PDF ' . ($_GET['type'] ?? ''); }
    }
    if ($action === null) return;

    $libelles = [
        'paiements' => 'Paiements Wave', 'seminaristes' => 'Séminaristes', 'commissions' => 'Membres commission',
        'edit_seminariste' => 'Modification séminariste', 'edit_membre' => 'Modification membre', 'edit_user' => 'Modification compte admin',
        'users' => 'Comptes admin', 'identifiants' => 'Identifiants', 'notes' => 'Saisie des notes', 'matieres' => 'Matières et résultats',
        'questions' => 'Questions du test', 'config_quiz' => 'Config quiz', 'test_entree' => "Test d'entrée", 'listes' => 'Listes',
        'dortoirs' => 'Dortoirs', 'parametres' => 'Paramètres du site', 'verifier' => 'Vérification de reçu', 'scanner' => 'Scanner de reçus', 'pdf' => 'Document',
        'critiques' => 'Critiques', 'correction' => 'Correction',
    ];
    $verbes = ['valider' => 'a validé', 'rejeter' => 'a rejeté', 'supprimer' => 'a supprimé', 'publier' => 'a publié', 'reset' => 'a réinitialisé'];
    $verbe = $verbes[$action] ?? ('a fait « ' . $action . ' »');
    $libelle = ($libelles[$script] ?? $script) . ' : ' . $verbe;

    // Personne / élément concerné (résolu AVANT l'exécution de l'action : l'élément peut être supprimé ensuite)
    $id = (int)($_POST['paiement_id'] ?? $_POST['id'] ?? $_GET['id'] ?? $_GET['supprimer'] ?? 0);
    $cible = '';
    try {
        if ($id > 0) {
            if ($script === 'paiements') {
                $st = $pdo->prepare("SELECT s.nom_prenoms FROM paiements p JOIN seminaristes s ON s.id = p.seminariste_id WHERE p.id = ?");
            } elseif (in_array($script, ['seminaristes', 'edit_seminariste', 'listes', 'notes', 'pdf', 'correction', 'seminariste_detail'], true)) {
                $tMembre = $script === 'pdf' && in_array($_GET['type'] ?? '', ['badge_com', 'diplome_com'], true);
                $st = $pdo->prepare($tMembre ? "SELECT nom_prenoms FROM membres_commission WHERE id = ?" : "SELECT nom_prenoms FROM seminaristes WHERE id = ?");
            } elseif (in_array($script, ['commissions', 'edit_membre'], true)) {
                $st = $pdo->prepare("SELECT nom_prenoms FROM membres_commission WHERE id = ?");
            } elseif (in_array($script, ['users', 'edit_user'], true)) {
                $st = $pdo->prepare("SELECT nom_affiche FROM comptes WHERE id = ?");
            } else { $st = null; }
            if ($st) { $st->execute([$id]); $cible = (string)$st->fetchColumn(); }
        }
    } catch (Throwable $e) { $cible = ''; }
    if ($cible === '' && $id > 0) { $cible = '#' . $id; }
    if ($script === 'pdf' && isset($_GET['tous'])) { $cible = 'tous'; }

    $compte = $u;
    register_shutdown_function(function () use ($pdo, $libelle, $cible, $compte) { journaliser($pdo, $libelle, $cible, $compte); });
}

/* ---------- Visiteurs (gérés par la commission Sécurité) ---------- */
function visiteursPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    rolesPreparer($pdo);
    $pdo->exec("CREATE TABLE IF NOT EXISTS visiteurs (
        id INT AUTO_INCREMENT PRIMARY KEY, nom_prenoms VARCHAR(150) NOT NULL, contact VARCHAR(20) NOT NULL,
        motif VARCHAR(255) NOT NULL DEFAULT '', heure_arrivee DATETIME NOT NULL, heure_sortie DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_vis_contact (contact), INDEX idx_vis_arrivee (heure_arrivee)) ENGINE=InnoDB");
    // table créée avant l'ajout du motif : on ajoute la colonne
    if (!$pdo->query("SHOW COLUMNS FROM visiteurs LIKE 'motif'")->fetch()) {
        $pdo->exec("ALTER TABLE visiteurs ADD COLUMN motif VARCHAR(255) NOT NULL DEFAULT '' AFTER contact");
    }
}

/** « 2026-12-24T14:30 » (champ datetime-local) -> « 2026-12-24 14:30:00 », ou null si invalide. */
function dateHeureSaisie($v) {
    $v = trim((string)$v);
    if ($v === '') return null;
    $t = strtotime(str_replace('T', ' ', $v));
    return $t ? date('Y-m-d H:i:s', $t) : null;
}
function dateHeureAffiche($v) { return $v ? date('d/m/Y H:i', strtotime($v)) : '-'; }
function dateHeureChamp($v) { return $v ? date('Y-m-d\TH:i', strtotime($v)) : ''; }

/** Ajoute le rôle « securite » à la colonne comptes.role si elle ne l'accepte pas encore (migration automatique). */
function rolesPreparer(PDO $pdo) {
    try {
        $col = $pdo->query("SHOW COLUMNS FROM comptes LIKE 'role'")->fetch();
        $type = (string)($col['Type'] ?? $col['type'] ?? '');
        if ($type !== '' && (stripos($type, "'securite'") === false || stripos($type, "'mg'") === false)) {
            $pdo->exec("ALTER TABLE comptes MODIFY role ENUM('membre','seminariste','admin','superadmin','finance','scientifique','securite','mg') NOT NULL DEFAULT 'membre'");
        }
    } catch (Throwable $e) { error_log('Migration rôle sécurité : ' . $e->getMessage()); }
}

/* ---------- Rapports journaliers des commissions ---------- */
function rapportsPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    $pdo->exec("CREATE TABLE IF NOT EXISTS rapports_journaliers (
        id INT AUTO_INCREMENT PRIMARY KEY, commission VARCHAR(100) NOT NULL, date_rapport DATE NOT NULL,
        activites TEXT NOT NULL, difficultes TEXT NULL, previsions TEXT NULL,
        auteur_id INT NULL, auteur_nom VARCHAR(150) NOT NULL DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL,
        INDEX idx_rapport_com (commission), INDEX idx_rapport_date (date_rapport)) ENGINE=InnoDB");
}

function normaliserCommission($c) {
    return strtr(mb_strtoupper(trim((string)$c), 'UTF-8'), ['É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'À' => 'A', 'Ç' => 'C']);
}

/** Commission du compte connecté (membre de commission ou compte Finance / Scientifique / Sécurité), ou null. */
function commissionPropre(PDO $pdo) {
    $u = utilisateurCourant();
    if (!$u) return null;
    $cible = null;
    if ($u['role'] === 'membre' && !empty($u['membre_id'])) {
        $st = $pdo->prepare("SELECT commission FROM membres_commission WHERE id = ?");
        $st->execute([$u['membre_id']]);
        $cible = (string)$st->fetchColumn();
    } elseif ($u['role'] === 'finance') { $cible = 'FINANCE'; }
    elseif ($u['role'] === 'scientifique') { $cible = 'SCIENTIFIQUE'; }
    elseif ($u['role'] === 'securite') { $cible = 'SÉCURITÉ'; }
    elseif ($u['role'] === 'admin' || $u['role'] === 'superadmin') { $cible = 'ADMINISTRATION'; }
    if (!$cible) return null;
    $n = normaliserCommission($cible);
    foreach (listeCommissions() as $c) { if (normaliserCommission($c) === $n) return $c; }
    return null;
}

/** Affiche un rapport journalier sous forme de lettre (texte justifié). $actions = HTML des boutons. */
function lettreRapportHtml(array $r, $actions = '') {
    $mois = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $fr = function ($d) use ($mois) { $t = strtotime($d); $j = (int)date('j', $t); return $j . ($j === 1 ? 'er' : '') . ' ' . $mois[(int)date('n', $t) - 1] . ' ' . date('Y', $t); };
    $h = '<article class="lettre"><div class="lettre-bande"></div><div class="lettre-entete"><img src="' . BASE_URL . '/assets/img/bulletin_entete.png" alt="AEEMCI - JOSPIA"></div>';
    $h .= '<div class="lettre-tete"><div><strong>Commission ' . e($r['commission']) . '</strong>' . (normaliserCommission($r['commission']) === 'ADMINISTRATION' ? '' : '<br><small>Responsable : ' . e($r['auteur_nom'] ?: '-') . '</small>') . '</div>';
    $h .= '<div class="lettre-lieu">Abidjan, le ' . e($fr($r['created_at'] ?: $r['date_rapport'])) . '</div></div>';
    $h .= '<div class="lettre-dest"><small>À l\'attention de</small><br><strong>Messieurs les Managers généraux</strong></div>';
    $h .= '<div class="lettre-objet">Objet : Rapport journalier du ' . e($fr($r['date_rapport'])) . '</div>';
    $h .= '<p class="lettre-p">Messieurs,</p>';
    $h .= '<p class="lettre-p lettre-j">Nous avons l\'honneur de vous rendre compte des activités de la commission ' . e($r['commission']) . ' pour la journée du ' . e($fr($r['date_rapport'])) . '.</p>';
    foreach ([['1. Activités réalisées', 'activites'], ['2. Difficultés rencontrées', 'difficultes'], ['3. Prévisions et besoins', 'previsions']] as [$lib, $cle]) {
        if (trim((string)($r[$cle] ?? '')) === '') continue;
        $h .= '<h4 class="lettre-titre">' . e($lib) . '</h4>';
        foreach (preg_split('/\R+/u', trim((string)$r[$cle])) as $para) { if (trim($para) !== '') $h .= '<p class="lettre-p lettre-j">' . e(trim($para)) . '</p>'; }
    }
    $h .= '<p class="lettre-p lettre-j">Veuillez agréer, Messieurs, l\'expression de nos salutations distinguées.</p>';
    $h .= '<div class="lettre-sign"><small>Le responsable de la commission ' . e($r['commission']) . ',</small>' . (normaliserCommission($r['commission']) === 'ADMINISTRATION' ? '' : '<br><strong>' . e($r['auteur_nom'] ?: '-') . '</strong>') . '</div>';
    $h .= '<div class="lettre-meta">Déposé le ' . e(date('d/m/Y H:i', strtotime($r['created_at']))) . (!empty($r['updated_at']) ? ' · modifié le ' . e(date('d/m/Y H:i', strtotime($r['updated_at']))) : '') . '</div>';
    if ($actions !== '') $h .= '<div class="lettre-actions no-print">' . $actions . '</div>';
    return $h . '</article>';
}

/* ------------------------------------------------------------------ */
/*  Inscription séminariste : création du dossier + inscriptions en attente de paiement  */
/* ------------------------------------------------------------------ */

/** Crée le séminariste, son compte et sa ligne de paiement. $d : données validées du formulaire. */
function creerInscriptionSeminariste(PDO $pdo, array $d, $nomPhoto, $referenceTx = '', $waveSessionId = null, $payeDeclare = 1) {
    $dortoir = affecterDortoir($pdo, $d['genre'], $d['age']);
    $matricule = genererMatriculeSeminariste($pdo);
    $niveauAffecte = ($dortoir === 'Pépinière') ? 'Pépinière' : null;
    $pdo->prepare("INSERT INTO seminaristes
        (nom_prenoms, genre, niveau_etude, anyama, section, sous_comite_final, lieu_residence, maladie, maladie_autre, age, contact, photo, parent_nom, parent_lien, parent_contact, matricule, dortoir, niveau_affecte)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute([
        $d['nom'], $d['genre'], $d['niveauEtude'], $d['anyama'], $d['section'], $d['section'], $d['lieuResidence'],
        $d['maladie'], $d['maladie'] === 'Autre' ? $d['maladieAutre'] : null, $d['age'], $d['contact'], $nomPhoto,
        $d['parentNom'], $d['parentLien'], $d['parentContact'], $matricule, $dortoir, $niveauAffecte
    ]);
    $sid = (int)$pdo->lastInsertId();

    [$identifiant, $motDePasse] = genererIdentifiantMotDePasse($d['contact']);
    $chk = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ?");
    $chk->execute([$identifiant]);
    if ($chk->fetchColumn() > 0) $identifiant .= '_' . $sid;
    $pdo->prepare("INSERT INTO comptes (identifiant, mot_de_passe, mdp_initial, role, seminariste_id, nom_affiche) VALUES (?,?,?,?,?,?)")
        ->execute([$identifiant, password_hash($motDePasse, PASSWORD_DEFAULT), $motDePasse, 'seminariste', $sid, $d['nom']]);

    paiementsPreparer($pdo);
    $pdo->prepare("INSERT INTO paiements (seminariste_id, reference_transaction, statut, numero_wave, montant, wave_session_id, paye_declare) VALUES (?, ?, 'en attente', ?, ?, ?, ?)")
        ->execute([$sid, (string)$referenceTx, $d['contact'], FRAIS_PARTICIPATION, $waveSessionId, $payeDeclare ? 1 : 0]);
    $pid = (int)$pdo->lastInsertId();
    return ['id' => $sid, 'paiement_id' => $pid, 'identifiant' => $identifiant, 'mdp' => $motDePasse, 'matricule' => $matricule, 'dortoir' => $dortoir];
}

function inscriptionsAttentePreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    $pdo->exec("CREATE TABLE IF NOT EXISTS inscriptions_attente (
        id INT AUTO_INCREMENT PRIMARY KEY, jeton VARCHAR(64) NOT NULL UNIQUE, donnees LONGTEXT NOT NULL, photo VARCHAR(255) NOT NULL,
        wave_session_id VARCHAR(100) NULL, statut VARCHAR(20) NOT NULL DEFAULT 'attente', seminariste_id INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, INDEX idx_att_session (wave_session_id)) ENGINE=InnoDB");
}

/** Enregistre une inscription EN ATTENTE de paiement (rien n'est créé dans les séminaristes). Retourne le jeton. */
function inscriptionEnAttente(PDO $pdo, array $d, $nomPhoto) {
    inscriptionsAttentePreparer($pdo);
    $jeton = bin2hex(random_bytes(24));
    $pdo->prepare("INSERT INTO inscriptions_attente (jeton, donnees, photo) VALUES (?,?,?)")->execute([$jeton, json_encode($d, JSON_UNESCAPED_UNICODE), $nomPhoto]);
    return $jeton;
}

/**
 * Le paiement est confirmé : crée le dossier du séminariste (une seule fois, même si le retour et le webhook arrivent ensemble)
 * puis valide le paiement. Retourne l'id du séminariste ou null.
 */
function finaliserInscriptionAttente(PDO $pdo, array $att, $waveSessionId, $transactionId = '') {
    inscriptionsAttentePreparer($pdo);
    if (!empty($att['seminariste_id'])) return (int)$att['seminariste_id'];
    $claim = $pdo->prepare("UPDATE inscriptions_attente SET statut = 'finalisation' WHERE id = ? AND statut = 'attente'");
    $claim->execute([$att['id']]);
    if ($claim->rowCount() < 1) {
        // déjà pris en charge par une autre requête : on attend brièvement son résultat
        for ($i = 0; $i < 10; $i++) {
            usleep(300000);
            $r = $pdo->prepare("SELECT seminariste_id FROM inscriptions_attente WHERE id = ?"); $r->execute([$att['id']]);
            if ($sid = (int)$r->fetchColumn()) return $sid;
        }
        return null;
    }
    try {
        $d = json_decode($att['donnees'], true);
        $r = creerInscriptionSeminariste($pdo, $d, $att['photo'], $transactionId !== '' ? $transactionId : 'WAVE-' . $waveSessionId, $waveSessionId);
        validerPaiement($pdo, $r['paiement_id'], null);
        $pdo->prepare("UPDATE inscriptions_attente SET statut = 'finalisee', seminariste_id = ? WHERE id = ?")->execute([$r['id'], $att['id']]);
        return $r['id'];
    } catch (Throwable $e) {
        error_log('Finalisation inscription : ' . $e->getMessage());
        $pdo->prepare("UPDATE inscriptions_attente SET statut = 'attente' WHERE id = ? AND seminariste_id IS NULL")->execute([$att['id']]);
        return null;
    }
}

/* ------------------------------------------------------------------ */
/*  QR code de la fiche d'inscription : vérification publique (vert = payé, rouge = refusé)  */
/* ------------------------------------------------------------------ */
function codeFichePreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    try {
        $col = $pdo->query("SHOW COLUMNS FROM seminaristes LIKE 'code_fiche'")->fetch();
        if (!$col) { $pdo->exec("ALTER TABLE seminaristes ADD COLUMN code_fiche VARCHAR(40) NULL, ADD UNIQUE INDEX uniq_code_fiche (code_fiche)"); }
    } catch (Throwable $e) { error_log('Migration code_fiche : ' . $e->getMessage()); }
}

/** Code secret (non devinable) du séminariste pour son QR code ; créé à la première demande. */
function codeFiche(PDO $pdo, $seminaristeId) {
    codeFichePreparer($pdo);
    $st = $pdo->prepare("SELECT code_fiche FROM seminaristes WHERE id = ?");
    $st->execute([(int)$seminaristeId]);
    $code = (string)$st->fetchColumn();
    if ($code === '') {
        $code = bin2hex(random_bytes(10));
        $pdo->prepare("UPDATE seminaristes SET code_fiche = ? WHERE id = ? AND (code_fiche IS NULL OR code_fiche = '')")->execute([$code, (int)$seminaristeId]);
        $st->execute([(int)$seminaristeId]);
        $code = (string)$st->fetchColumn();
    }
    return $code;
}

/** Statut de paiement d'un séminariste : 'validé' | 'en attente' | 'rejeté' | 'aucun'. */
function statutPaiementSeminariste(PDO $pdo, $seminaristeId) {
    $st = $pdo->prepare("SELECT statut FROM paiements WHERE seminariste_id = ? ORDER BY (statut = 'validé') DESC, id DESC LIMIT 1");
    $st->execute([(int)$seminaristeId]);
    $v = $st->fetchColumn();
    return $v === false ? 'aucun' : (string)$v;
}

/** Adresse lue par le QR code de la fiche. */
function urlVerificationFiche(PDO $pdo, $seminaristeId) {
    return urlSite() . '/verification?c=' . codeFiche($pdo, $seminaristeId);
}

/** Bloc HTML « QR code de la fiche » (nécessite assets/js/vendor/qrcode.js). */
function blocQrFiche(PDO $pdo, $seminaristeId) {
    $url = urlVerificationFiche($pdo, $seminaristeId);
    $statut = statutPaiementSeminariste($pdo, $seminaristeId);
    $badge = $statut === 'validé' ? '<span class="pill pill-vert">✔ Paiement validé</span>' : ($statut === 'rejeté' ? '<span class="pill" style="background:#fde8e8;color:#b42318;">✖ Paiement rejeté</span>' : '<span class="pill pill-or">⏳ Paiement en attente</span>');
    return '<div class="fiche-qr" style="text-align:center;margin:18px 0 6px;"><div id="qr-fiche" data-url="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;"></div>'
        . '<div style="font-size:.82rem;color:var(--texte-doux);margin:6px 0;">Scannez ce QR code : <strong style="color:#0a8f3c;">vert = payé</strong>, <strong style="color:#c81e1e;">rouge = refusé</strong></div>' . $badge . '</div>'
        . '<script src="' . BASE_URL . '/assets/js/vendor/qrcode.js"></script><script>(function(){var el=document.getElementById("qr-fiche");if(!el||typeof qrcode==="undefined")return;var q=qrcode(0,"M");q.addData(el.getAttribute("data-url"));q.make();el.innerHTML=q.createSvgTag(5,2);})();</script>';
}

/* ------------------------------------------------------------------ */
/*  Autorisations de sortie du camp : demande -> MG/MGA -> Sécurité -> retour  */
/* ------------------------------------------------------------------ */
function sortiesPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    $pdo->exec("CREATE TABLE IF NOT EXISTS sorties (
        id INT AUTO_INCREMENT PRIMARY KEY, compte_id INT NOT NULL, type VARCHAR(20) NOT NULL DEFAULT 'membre', personne_id INT NULL,
        nom VARCHAR(150) NOT NULL, groupe VARCHAR(100) NOT NULL DEFAULT '', motif VARCHAR(255) NOT NULL,
        heure_sortie DATETIME NOT NULL, heure_retour DATETIME NOT NULL, statut VARCHAR(20) NOT NULL DEFAULT 'attente_mg',
        mg_nom VARCHAR(150) NULL, mg_at DATETIME NULL, refus_motif VARCHAR(255) NULL, secu_nom VARCHAR(150) NULL, secu_at DATETIME NULL,
        rentre_at DATETIME NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sortie_compte (compte_id), INDEX idx_sortie_statut (statut)) ENGINE=InnoDB");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sorties_roles (compte_id INT NOT NULL, role VARCHAR(10) NOT NULL, PRIMARY KEY (compte_id, role)) ENGINE=InnoDB");
    try {
        if (!$pdo->query("SHOW COLUMNS FROM sorties LIKE 'refus_vu'")->fetch()) { $pdo->exec("ALTER TABLE sorties ADD COLUMN refus_vu TINYINT NOT NULL DEFAULT 0"); }
    } catch (Throwable $e) { error_log('Migration sorties : ' . $e->getMessage()); }
}

/** Le super administrateur attribue le rôle « mg » (validation) ou « securite » (confirmation) à des comptes. */
function sortieRoleAttribue(PDO $pdo, $compteId, $role) {
    sortiesPreparer($pdo);
    $st = $pdo->prepare("SELECT COUNT(*) FROM sorties_roles WHERE compte_id = ? AND role = ?");
    $st->execute([(int)$compteId, $role]);
    return $st->fetchColumn() > 0;
}

/** Valide les demandes de sortie : administrateurs, ou compte à qui le super administrateur a attribué « MG / MGA ». */
function estMG() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $u = utilisateurCourant();
    if (!$u) return $cache = false;
    if (in_array($u['role'], ['admin', 'superadmin', 'mg'], true)) return $cache = true;
    global $pdo;
    try { return $cache = sortieRoleAttribue($pdo, $u['id'], 'mg'); } catch (Throwable $e) { return $cache = false; }
}

/** Confirme les sorties (bouton OK) : commission Sécurité (et administrateurs). */
function estSortieSecurite() {
    return estSecurite();
}

/** Identité de la personne connectée pour une demande de sortie : [type, personne_id, nom, groupe]. */
function sortiePersonne(PDO $pdo, array $u) {
    if ($u['role'] === 'seminariste' && !empty($u['seminariste_id'])) {
        $st = $pdo->prepare("SELECT nom_prenoms, dortoir, niveau_affecte FROM seminaristes WHERE id = ?");
        $st->execute([$u['seminariste_id']]);
        $r = $st->fetch();
        return ['seminariste', (int)$u['seminariste_id'], (string)($r['nom_prenoms'] ?? $u['nom_affiche']), 'Séminariste - ' . ($r['niveau_affecte'] ?: ($r['dortoir'] ?? ''))];
    }
    if ($u['role'] === 'membre' && !empty($u['membre_id'])) {
        $st = $pdo->prepare("SELECT nom_prenoms, commission FROM membres_commission WHERE id = ?");
        $st->execute([$u['membre_id']]);
        $r = $st->fetch();
        return ['membre', (int)$u['membre_id'], (string)($r['nom_prenoms'] ?? $u['nom_affiche']), 'Commission ' . ($r['commission'] ?? '')];
    }
    $com = commissionPropre($pdo);
    return ['membre', null, (string)($u['nom_affiche'] ?: $u['identifiant']), $com ? 'Commission ' . $com : ucfirst((string)$u['role'])];
}

function libelleStatutSortie($st) {
    return ['attente_mg' => ['⏳ En attente du MG / MGA', 'pill-or'], 'refusee' => ['✖ Refusée', 'pill-gris'], 'attente_securite' => ['⏳ Validée par le MG - en attente de la Sécurité', 'pill-or'],
            'autorisee' => ['✔ Autorisation accordée', 'pill-vert'], 'rentre' => ['🏠 Rentré sur le camp', 'pill-vert'], 'annulee' => ['Annulée', 'pill-gris']][$st] ?? [$st, 'pill-gris'];
}

/** Nombre de demandes que la personne connectée doit traiter (pastille du menu). */
function sortiesATraiter(PDO $pdo) {
    try {
        $n = 0;
        sortiesPreparer($pdo);
        if (estMG()) $n += (int)$pdo->query("SELECT COUNT(*) FROM sorties WHERE statut = 'attente_mg'")->fetchColumn();
        if (estSortieSecurite()) $n += (int)$pdo->query("SELECT COUNT(*) FROM sorties WHERE statut = 'attente_securite'")->fetchColumn();
        return $n;
    } catch (Throwable $e) { return 0; }
}

/** Sortie autorisée en cours de la personne (pour l'alerte « heure épuisée »), ou null. */
function sortieEnCours(PDO $pdo, $compteId) {
    try {
        sortiesPreparer($pdo);
        $st = $pdo->prepare("SELECT * FROM sorties WHERE compte_id = ? AND statut = 'autorisee' ORDER BY id DESC LIMIT 1");
        $st->execute([(int)$compteId]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}

/** Dernière demande refusée que la personne n'a pas encore « vue » (message de refus), ou null. */
function sortieRefusee(PDO $pdo, $compteId) {
    try {
        sortiesPreparer($pdo);
        $st = $pdo->prepare("SELECT * FROM sorties WHERE compte_id = ? AND statut = 'refusee' AND refus_vu = 0 ORDER BY id DESC LIMIT 1");
        $st->execute([(int)$compteId]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}


/* ------------------------------------------------------------------ */
/*  Cadrage automatique des photos (visage centré dans le cercle / cadre du badge)  */
/* ------------------------------------------------------------------ */
/**
 * Position (px, py) entre 0 et 1 pour un affichage « cover » dans une fenêtre de rapport $ratio (largeur / hauteur) :
 * on repère les pixels « couleur de peau » et on centre la fenêtre sur le visage. Repli : [0.5, 0.25].
 */
function photoFocus(PDO $pdo, $nom, $ratio = 1.0) {
    static $cache = [];
    $cle = $nom . '|' . round($ratio, 3);
    if (isset($cache[$cle])) return $cache[$cle];
    $defaut = [0.5, 0.25];
    if (!$nom || !function_exists('imagecreatefromstring') || !preg_match('/^[A-Za-z0-9._-]{1,120}$/', $nom)) return $cache[$cle] = $defaut;
    try {
        $donnees = null;
        $f = __DIR__ . '/../uploads/photos/' . $nom;
        if (is_file($f)) $donnees = @file_get_contents($f);
        if (!$donnees) {
            photoTableBase($pdo);
            $st = $pdo->prepare("SELECT donnees FROM photos_stockees WHERE nom = ?");
            $st->execute([$nom]);
            $donnees = $st->fetchColumn();
        }
        $im = $donnees ? @imagecreatefromstring($donnees) : null;
        if (!$im) return $cache[$cle] = $defaut;
        $w0 = imagesx($im); $h0 = imagesy($im);
        $w = min(80, $w0); $h = max(1, (int)round($h0 * $w / $w0));
        $p = imagecreatetruecolor($w, $h);
        imagecopyresampled($p, $im, 0, 0, 0, 0, $w, $h, $w0, $h0);
        imagedestroy($im);
        $xs = []; $ys = [];
        for ($y = 0; $y < $h; $y++) for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($p, $x, $y);
            $r = ($c >> 16) & 255; $g = ($c >> 8) & 255; $b = $c & 255;
            $Y = 0.299 * $r + 0.587 * $g + 0.114 * $b;
            $cb = 128 - 0.168736 * $r - 0.331264 * $g + 0.5 * $b;
            $cr = 128 + 0.5 * $r - 0.418688 * $g - 0.081312 * $b;
            // pixel de peau (large pour couvrir les peaux foncées), pondéré vers le centre de l'image
            if ($Y > 35 && $Y < 235 && $cb > 80 && $cb < 125 && $cr > 143 && $cr < 178 && $r > $b) {
                $dx = abs($x / $w - 0.5); if ($dx > 0.42) continue;
                $xs[] = $x; $ys[] = $y;
            }
        }
        imagedestroy($p);
        if (count($xs) < max(30, $w * $h * 0.03)) return $cache[$cle] = $defaut;
        sort($xs); sort($ys);
        $n = count($xs);
        $fx = $xs[(int)($n * 0.5)] / $w;
        $fy = $ys[(int)($n * 0.38)] / $h; // plutôt vers le haut de la zone de peau : le visage, pas le cou
        $ia = $w0 / $h0;
        $pos = [0.5, 0.5];
        if ($ia > $ratio) { // image plus large que la fenêtre : on décale horizontalement
            $vis = $ratio / $ia; // part visible de la largeur
            $pos[0] = $vis >= 1 ? 0.5 : max(0, min(1, ($fx - $vis / 2) / (1 - $vis)));
            $pos[1] = 0.5;
        } else { // image plus haute : on décale verticalement
            $vis = $ia / $ratio; // part visible de la hauteur
            $pos[1] = $vis >= 1 ? 0.5 : max(0, min(1, ($fy - $vis * 0.46) / (1 - $vis)));
        }
        return $cache[$cle] = $pos;
    } catch (Throwable $e) { return $cache[$cle] = $defaut; }
}


/* ------------------------------------------------------------------ */
/*  Noms des dortoirs : les quatre Califes (frères) et quatre figures féminines (sœurs)  */
/* ------------------------------------------------------------------ */
const DORTOIRS_FRERES = ['ABU BAKR AS-SIDDIQ', 'OUMAR IBN AL-KHATTAB', 'OUTHMAN IBN AFFAN', 'ALI IBN ABI TALIB'];
const DORTOIRS_SOEURS = ['KHADÎDJA BINT KHOUWAYLID', 'AÏCHA BINT ABOU BAKR', 'FATIMA BINT MUHAMMAD', 'HAFSA BINT OUMAR'];

/** Renomme (une seule fois) « Dortoir Hommes 1..4 / Femmes 1..4 » dans la table dortoirs et chez les séminaristes. */
function dortoirsRenommer(PDO $pdo) {
    static $fait = false;
    if ($fait) return;
    $fait = true;
    try {
        $st = $pdo->query("SELECT COUNT(*) FROM dortoirs WHERE nom LIKE 'Dortoir Hommes %' OR nom LIKE 'Dortoir Femmes %'");
        if ((int)$st->fetchColumn() === 0) return;
        foreach ([['Hommes', DORTOIRS_FRERES], ['Femmes', DORTOIRS_SOEURS]] as [$groupe, $noms]) {
            foreach ($noms as $i => $nouveau) {
                $ancien = 'Dortoir ' . $groupe . ' ' . ($i + 1);
                $pdo->prepare("UPDATE dortoirs SET nom = ? WHERE nom = ?")->execute([$nouveau, $ancien]);
                $pdo->prepare("UPDATE seminaristes SET dortoir = ? WHERE dortoir = ?")->execute([$nouveau, $ancien]);
            }
        }
    } catch (Throwable $e) { error_log('Renommage dortoirs : ' . $e->getMessage()); }
}


/** Table des programmes journaliers (fichier stocké en base : disque éphémère sur Render). */
function programmePreparer(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS programmes_journaliers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(160) NOT NULL,
        nom_fichier VARCHAR(160) NOT NULL,
        mime VARCHAR(60) NOT NULL,
        donnees LONGBLOB NOT NULL,
        publie TINYINT(1) NOT NULL DEFAULT 0,
        auteur VARCHAR(120) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

/** Bandeau affiché à tout le monde quand un programme journalier est publié. */
function programmeBandeau(PDO $pdo) {
    try {
        programmePreparer($pdo);
        $p = $pdo->query("SELECT id, titre, created_at FROM programmes_journaliers WHERE publie = 1 ORDER BY id DESC LIMIT 1")->fetch();
    } catch (Throwable $e) { return ''; }
    if (!$p) return '';
    $u = BASE_URL . '/programme?id=' . (int)$p['id'];
    return '<div class="programme-bandeau"><span class="pb-ico">📅</span><div><strong>Programme journalier</strong><br><small>' . e($p['titre']) . '</small></div>'
        . '<a class="btn btn-or btn-sm" href="' . $u . '" target="_blank" rel="noopener">Voir</a> '
        . '<a class="btn btn-outline btn-sm" href="' . $u . '&dl=1">Télécharger</a></div>';
}

/** Colonne « responsable de commission » sur les membres (cochée par les administrateurs). */
function responsablePreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    try {
        if (!$pdo->query("SHOW COLUMNS FROM membres_commission LIKE 'responsable'")->fetch()) {
            $pdo->exec("ALTER TABLE membres_commission ADD responsable TINYINT(1) NOT NULL DEFAULT 0");
        }
    } catch (Throwable $e) { error_log('Migration responsable : ' . $e->getMessage()); }
}

/** Le compte connecté est-il membre ET responsable de sa commission ? */
function estResponsableCommission(PDO $pdo) {
    $u = utilisateurCourant();
    if (!$u || $u['role'] !== 'membre' || empty($u['membre_id'])) return false;
    responsablePreparer($pdo);
    try {
        $st = $pdo->prepare("SELECT responsable FROM membres_commission WHERE id = ?");
        $st->execute([$u['membre_id']]);
        return (int)$st->fetchColumn() === 1;
    } catch (Throwable $e) { return false; }
}

/** Renomme la section « SOUNTIATA KEÏTA » en « SOUNDJATA KEÏTA » pour les inscriptions déjà enregistrées. */
function sectionsRenommer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    try {
        $st = $pdo->prepare("UPDATE seminaristes SET section = 'SOUNDJATA KEÏTA' WHERE section = 'SOUNTIATA KEÏTA'");
        $st->execute();
    } catch (Throwable $e) { error_log('Renommage section : ' . $e->getMessage()); }
}


/** Liens courts partageables (/l/CODE), créés uniquement par le super administrateur. */
const LIENS_COURTS = [
    'seminariste' => ['📚 Formulaire des séminaristes', '/inscription_seminariste', 6],
    'commission'  => ['👥 Formulaire des membres de commission', '/inscription_commission', 6],
    'visiteur'    => ['🚶 Formulaire des visiteurs', '/visiteur', 6],
    'niveaux'     => ['🎚️ Niveaux selon les notes (commission scientifique)', null, 10],
];
function liensCourtsPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    $pdo->exec("CREATE TABLE IF NOT EXISTS liens_courts (cible VARCHAR(20) NOT NULL PRIMARY KEY, code VARCHAR(20) NOT NULL UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
}
function codeCourt($longueur) {
    $alpha = 'abcdefghjkmnpqrstuvwxyz23456789';
    $c = '';
    for ($i = 0; $i < $longueur; $i++) { $c .= $alpha[random_int(0, strlen($alpha) - 1)]; }
    return $c;
}


/** Colonne « paye_declare » : 1 = la personne a indiqué avoir payé (à valider par la Finance), 0 = en attente de paiement. */
function paiementsPreparer(PDO $pdo) {
    static $ok = false;
    if ($ok) return;
    $ok = true;
    try {
        if (!$pdo->query("SHOW COLUMNS FROM paiements LIKE 'paye_declare'")->fetch()) {
            $pdo->exec("ALTER TABLE paiements ADD paye_declare TINYINT(1) NOT NULL DEFAULT 0");
            // Paiements déjà en attente avant cette évolution : le parcours impliquait que la personne avait payé
            $pdo->exec("UPDATE paiements SET paye_declare = 1 WHERE statut = 'en attente'");
        }
    } catch (Throwable $e) { error_log('Migration paye_declare : ' . $e->getMessage()); }
}
