<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
require_once __DIR__ . '/../includes/pdf_docs.php';

$type = $_GET['type'] ?? '';
$ids = array_values(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? ($_GET['id'] ?? ''))))));
$tous = !empty($_GET['tous']);

function lignesPar(PDO $pdo, $table, array $ids, $tous, $where = '', array $params = [], $ordre = 'nom_prenoms') {
    if ($tous) {
        $st = $pdo->prepare("SELECT * FROM $table WHERE 1=1 $where ORDER BY $ordre");
        $st->execute($params);
        return $st->fetchAll();
    }
    if (!$ids) return [];
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT * FROM $table WHERE id IN ($in) ORDER BY $ordre");
    $st->execute($ids);
    return $st->fetchAll();
}

switch ($type) {
    case 'badge_sem':
        $w = ''; $p = [];
        if (($_GET['dortoir'] ?? '') !== '') { $w .= ' AND dortoir = ?'; $p[] = $_GET['dortoir']; }
        if (($_GET['niveau'] ?? '') === 'none') { $w .= " AND (niveau_affecte IS NULL OR niveau_affecte = '')"; }
        elseif (($_GET['niveau'] ?? '') !== '') { $w .= ' AND niveau_affecte = ?'; $p[] = $_GET['niveau']; }
        $l = lignesPar($pdo, 'seminaristes', $ids, $tous, $w, $p, 'dortoir, nom_prenoms');
        if (!$l) { die('Aucun badge à télécharger.'); }
        $suffixe = ($_GET['niveau'] ?? '') !== '' ? '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $_GET['niveau']) : '';
        pdfBadgesA4($l, function ($s) use ($pdo) { return pdfBadgeSeminariste($pdo, $s); }, 'badges_seminaristes' . $suffixe . '.pdf');
    case 'badge_com':
        $w = ''; $p = [];
        if (($_GET['commission'] ?? '') !== '') { $w .= ' AND commission = ?'; $p[] = $_GET['commission']; }
        $l = lignesPar($pdo, 'membres_commission', $ids, $tous, $w, $p, 'commission, nom_prenoms');
        if (!$l) { die('Aucun badge à télécharger.'); }
        $suffixe = ($_GET['commission'] ?? '') !== '' ? '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $_GET['commission']) : '';
        pdfBadgesA4($l, function ($m) use ($pdo) { return pdfBadgeCommission($pdo, $m); }, 'badges_commission' . $suffixe . '.pdf');
    case 'diplome_sem':
        $w = " AND (test_complete = 1 OR niveau_affecte = 'Pépinière')"; $p = [];
        if (($_GET['niveau'] ?? '') !== '') { $w .= ' AND niveau_affecte = ?'; $p[] = $_GET['niveau']; }
        $l = array_values(array_filter(lignesPar($pdo, 'seminaristes', $ids, $tous, $w, $p), function ($s) { return !empty($s['test_complete']) || $s['niveau_affecte'] === 'Pépinière'; }));
        if (!$l) { die("Aucun diplôme disponible (test d'entrée non complété)."); }
        $nomF = $tous ? 'diplomes_seminaristes' . (($_GET['niveau'] ?? '') !== '' ? '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $_GET['niveau']) : '') : 'diplome_' . preg_replace('/[^A-Za-z0-9]+/', '_', $l[0]['nom_prenoms']);
        pdfDiplomesA4($l, function ($s) { return pdfDiplomeSeminariste($s['nom_prenoms']); }, $nomF . '.pdf');
    case 'diplome_com':
        $w = ''; $p = [];
        if (($_GET['commission'] ?? '') !== '') { $w .= ' AND commission = ?'; $p[] = $_GET['commission']; }
        $l = lignesPar($pdo, 'membres_commission', $ids, $tous, $w, $p, 'commission, nom_prenoms');
        if (!$l) { die("Aucun diplôme à télécharger."); }
        $nomF = $tous ? 'diplomes_commission' . (($_GET['commission'] ?? '') !== '' ? '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $_GET['commission']) : '') : 'diplome_' . preg_replace('/[^A-Za-z0-9]+/', '_', $l[0]['nom_prenoms']);
        pdfDiplomesA4($l, function ($m) { return pdfDiplomeCommission($m['nom_prenoms'], 'MEMBRE DE LA COMMISSION ' . nomCommissionComplet($m['commission'])); }, $nomF . '.pdf');
    default:
        http_response_code(400);
        die('Type de document inconnu.');
}
