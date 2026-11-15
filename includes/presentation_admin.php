<?php
/** Présentation de la commission Administration (bataille de Kirina) : contenu modifiable depuis le site + chiffres en direct. */
const PRES_ADMIN_RUBRIQUES = [
    'effectue'     => ['AUTRES RÉALISATIONS', 'Travaux effectués', 'Un travail par ligne.'],
    'non_effectue' => ['TRAVAUX NON EFFECTUÉS', 'Travaux non effectués', 'Un travail par ligne (ce qui reste à faire).'],
    'difficultes'  => ['DIFFICULTÉS RENCONTRÉES', 'Difficultés rencontrées', 'Une difficulté par ligne.'],
    'perspectives' => ['PERSPECTIVES ET SUGGESTIONS', 'Perspectives et suggestions (facultatif)', 'Une proposition par ligne. Laissez vide pour ne pas afficher cette diapositive.'],
];
/** Texte proposé par défaut tant que la rubrique n'a jamais été enregistrée depuis le formulaire. */
const PRES_ADMIN_EFFECTUE_DEFAUT = "Diffusion des informations sur les réunions et les audiences\nAnimation des réunions, en ligne comme en présentiel\nRédaction des courriers\nParticipation aux différentes audiences tenues";
const PRES_ADMIN_NONEFFECTUE_DEFAUT = "Rédaction des procès-verbaux (PV) des réunions\nRetard dans la rédaction des contenus";
function presAdminTable() {
    static $ok = false; global $pdo;
    if ($ok) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS pres_administration (cle VARCHAR(30) NOT NULL PRIMARY KEY, valeur MEDIUMTEXT NOT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ok = true;
}
function presAdminLire() {
    global $pdo;
    $r = array_fill_keys(array_keys(PRES_ADMIN_RUBRIQUES), '');
    try {
        presAdminTable();
        $vu = [];
        foreach ($pdo->query("SELECT cle, valeur FROM pres_administration")->fetchAll() as $l) { if (isset($r[$l['cle']])) { $r[$l['cle']] = (string)$l['valeur']; $vu[$l['cle']] = true; } }
        if (empty($vu['effectue'])) $r['effectue'] = PRES_ADMIN_EFFECTUE_DEFAUT;
        if (empty($vu['non_effectue'])) $r['non_effectue'] = PRES_ADMIN_NONEFFECTUE_DEFAUT;
    } catch (Throwable $e) { $r['effectue'] = PRES_ADMIN_EFFECTUE_DEFAUT; $r['non_effectue'] = PRES_ADMIN_NONEFFECTUE_DEFAUT; }
    return $r;
}
function presAdminEnregistrer(array $valeurs) {
    global $pdo;
    presAdminTable();
    $st = $pdo->prepare("INSERT INTO pres_administration (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
    foreach (array_keys(PRES_ADMIN_RUBRIQUES) as $k) {
        $v = str_replace(["\r\n", "\r"], "\n", (string)($valeurs[$k] ?? ''));
        $st->execute([$k, mb_substr($v, 0, 20000)]);
    }
}
function presAdminLignes($texte) {
    $o = [];
    foreach (explode("\n", str_replace("\r", '', (string)$texte)) as $l) {
        $l = trim(preg_replace('/^\s*(?:[-•*–]|\d+[.)])\s*/u', '', $l));
        if ($l !== '') $o[] = $l;
    }
    return $o;
}
function presAdminChiffres() {
    global $pdo;
    $n = function ($sql) use ($pdo) { try { return (int)$pdo->query($sql)->fetchColumn(); } catch (Throwable $e) { return null; } };
    return [
        'Séminaristes inscrits' => $n("SELECT COUNT(*) FROM seminaristes"),
        'Séminaristes affectés à un dortoir' => $n("SELECT COUNT(*) FROM seminaristes WHERE dortoir IS NOT NULL AND dortoir <> ''"),
        "Tests d'entrée complétés" => $n("SELECT COUNT(*) FROM seminaristes WHERE test_complete = 1"),
        'Membres de commission enregistrés' => $n("SELECT COUNT(*) FROM membres_commission"),
        'Membres de la commission Administration' => $n("SELECT COUNT(*) FROM membres_commission WHERE UPPER(commission) = 'ADMINISTRATION'"),
        "Capacité d'accueil des dortoirs (places)" => $n("SELECT COALESCE(SUM(capacite),0) FROM dortoirs"),
    ];
}
/** Diapositives de contenu : [numéro, titre, lignes, gras, deux colonnes]. Les rubriques vides sont ignorées. */
function presAdminDiapositives(array $contenu, array $chiffres) {
    $s = [];
    $rom = ['II-', 'III-', 'IV-']; $r = 0; // « I- » = Réalisations (diapositives des badges)
    foreach (PRES_ADMIN_RUBRIQUES as $k => $def) {
        $lignes = presAdminLignes($contenu[$k] ?? '');
        if (!$lignes) continue;
        $num = ($k === 'effectue') ? '' : ($rom[$r++] ?? '');
        $parts = array_chunk($lignes, 6);
        foreach ($parts as $i => $p) { $s[] = [$i === 0 ? $num : '', $def[0] . ($i > 0 ? ' (SUITE)' : ''), $p, false, false]; }
    }
    return $s;
}


/** Clé de comparaison de noms (majuscules, sans accents, sans espaces multiples). */
function presAdminCleNom($n) {
    $n = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string)$n)));
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT', $n);
    return preg_replace('/[^A-Z ]/', '', $t !== false ? $t : $n);
}

/** Membres de la commission Administration : fiches détaillées (includes/pres_membres.php) puis membres inscrits sur le site. */
function presAdminMembres() {
    $r = []; $vu = [];
    $fiches = is_file(__DIR__ . '/pres_membres.php') ? (include __DIR__ . '/pres_membres.php') : [];
    foreach ($fiches as $f) {
        $f['resp'] = false;
        $chemin = __DIR__ . '/../assets/img/pres/membres/' . $f['photo'];
        $f['src'] = BASE_URL . '/assets/img/pres/membres/' . rawurlencode($f['photo']) . (is_file($chemin) ? '?v=' . filemtime($chemin) : '');
        $f['fiche'] = true; $r[presAdminCleNom($f['nom'])] = $f;
    }
    try {
        $pdo = $GLOBALS['pdo'];
        responsablePreparer($pdo);
        foreach ($pdo->query("SELECT nom_prenoms, commission, photo, responsable FROM membres_commission ORDER BY responsable DESC, nom_prenoms ASC")->fetchAll() as $m) {
            if (normaliserCommission($m['commission']) !== 'ADMINISTRATION') continue;
            $k = presAdminCleNom($m['nom_prenoms']);
            if (isset($r[$k])) { $r[$k]['resp'] = (int)$m['responsable'] === 1; continue; }
            $r[$k] = ['nom' => (string)$m['nom_prenoms'], 'src' => $m['photo'] !== '' ? BASE_URL . '/uploads/photos/' . rawurlencode((string)$m['photo']) : '', 'resp' => (int)$m['responsable'] === 1, 'fiche' => false];
        }
    } catch (Throwable $e) { }
    return array_values($r);
}
