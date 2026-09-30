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
function sectionsParAnyama() {
    return [
        'Anyama 1' => ['LYMA', 'SAINT MICHEL', 'ATLAS', 'LYMAO', 'YVAC', 'GAOUSSOU', 'LA PERRUCHE', 'Autre'],
        'Anyama 2' => ['GSAMAT', 'BUTHMAAN', 'SOUNTIATA KEÏTA', 'Autre'],
    ];
}

function listeCommissions() {
    return ['MG', 'MGA', 'ADMINISTRATION', 'SCIENTIFIQUE', 'MIC', 'FINANCE', 'SANTÉ', 'SÉCURITÉ', 'HYGIÈNE', 'PÉPINIÈRE', 'RESTAURATION', 'LOGISTIQUE', 'PROTOCOLE'];
}

/** Calcule le niveau d'affectation académique/spirituel à partir de la note /20 */
function determinerNiveauTest($note) {
    if ($note < 5)  return 'Primaire';
    if ($note < 9)  return 'Secondaire';
    if ($note < 13) return 'Universitaire';
    return 'Leader';
}

/** Indique si le Super Admin / Admin a publié les résultats (bulletins visibles par les séminaristes) */
function resultatsPublies(PDO $pdo) {
    $stmt = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'resultats_publies'");
    $stmt->execute();
    return $stmt->fetchColumn() === '1';
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
function validerPaiement(PDO $pdo, int $paiementId, int $validateurId): bool {
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
