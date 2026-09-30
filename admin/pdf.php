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
        if (($_GET['niveau'] ?? '') !== '') { $w .= ' AND niveau_affecte = ?'; $p[] = $_GET['niveau']; }
        $l = lignesPar($pdo, 'seminaristes', $ids, $tous, $w, $p, 'dortoir, nom_prenoms');
        pdfBadgesA4($l, function ($s) use ($pdo) { return pdfBadgeSeminariste($pdo, $s); }, 'badges_seminaristes.pdf');
    case 'badge_com':
        $w = ''; $p = [];
        if (($_GET['commission'] ?? '') !== '') { $w .= ' AND commission = ?'; $p[] = $_GET['commission']; }
        $l = lignesPar($pdo, 'membres_commission', $ids, $tous, $w, $p, 'commission, nom_prenoms');
        pdfBadgesA4($l, function ($m) use ($pdo) { return pdfBadgeCommission($pdo, $m); }, 'badges_commission.pdf');
    case 'diplome_sem':
        $l = array_values(array_filter(lignesPar($pdo, 'seminaristes', $ids, false), function ($s) { return !empty($s['test_complete']); }));
        if (!$l) { die("Diplôme indisponible (test d'entrée non complété)."); }
        pdfDiplomesA4($l, function ($s) { return pdfDiplomeSeminariste($s['nom_prenoms']); }, 'diplome_' . preg_replace('/[^A-Za-z0-9]+/', '_', $l[0]['nom_prenoms']) . '.pdf');
    case 'diplome_com':
        $l = lignesPar($pdo, 'membres_commission', $ids, false);
        if (!$l) { die("Membre introuvable."); }
        pdfDiplomesA4($l, function ($m) { return pdfDiplomeCommission($m['nom_prenoms'], 'MEMBRE DE LA COMMISSION ' . $m['commission']); }, 'diplome_' . preg_replace('/[^A-Za-z0-9]+/', '_', $l[0]['nom_prenoms']) . '.pdf');
    default:
        http_response_code(400);
        die('Type de document inconnu.');
}
