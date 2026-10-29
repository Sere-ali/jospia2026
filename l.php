<?php
/** Lien court /l/CODE : redirige vers un formulaire ou ouvre la page « niveaux » (commission scientifique). */
require_once __DIR__ . '/includes/init.php';
$code = (string)($_GET['c'] ?? '');
$cible = null;
if (preg_match('/^[a-z0-9]{4,20}$/', $code)) {
    try {
        liensCourtsPreparer($pdo);
        $st = $pdo->prepare("SELECT cible FROM liens_courts WHERE code = ?");
        $st->execute([$code]);
        $cible = $st->fetchColumn() ?: null;
    } catch (Throwable $e) { $cible = null; }
}
if (!$cible || !isset(LIENS_COURTS[$cible])) {
    http_response_code(404);
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Lien invalide</h2><p>Ce lien n\'est plus actif. Demandez un nouveau lien à l\'administration.</p></div>');
}
if ($cible === 'niveaux') {
    header('X-Robots-Tag: noindex, nofollow');
    $PARTAGE = true;
    require __DIR__ . '/admin/seuils_niveaux.php';
    exit;
}
if ($cible === 'logistique') {
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');
    $st = $pdo->prepare("SELECT nom_prenoms, contact, matricule FROM membres_commission WHERE UPPER(commission) = 'LOGISTIQUE' ORDER BY nom_prenoms");
    $st->execute();
    $liste = $st->fetchAll();
    if (isset($_GET['export'])) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="commission_logistique.csv"');
        echo "\xEF\xBB\xBF";
        $f = fopen('php://output', 'w');
        fputcsv($f, ['N°', 'Nom et prénoms', 'Contact', 'Matricule'], ';');
        foreach ($liste as $i => $m) { fputcsv($f, [$i + 1, $m['nom_prenoms'], $m['contact'], $m['matricule']], ';'); }
        exit;
    }
    $titrePage = 'Commission Logistique';
    require __DIR__ . '/includes/header.php';
    ?>
<section class="section"><div class="container" style="max-width:820px;">
    <div class="section-titre"><span class="eyebrow">JOSPIA 2026</span><h2>Commission Logistique</h2></div>
    <p class="no-print" style="text-align:center;"><?= count($liste) ?> membre(s) &middot; <a href="?export=1" class="btn btn-outline btn-sm">Exporter en Excel</a> <a href="#" onclick="window.print();return false;" class="btn btn-outline btn-sm">Imprimer</a></p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>N°</th><th>Nom et prénoms</th><th>Contact</th><th>Matricule</th></tr></thead>
            <tbody>
            <?php foreach ($liste as $i => $m): ?>
                <tr><td><?= $i + 1 ?></td><td><?= e($m['nom_prenoms']) ?></td><td><?= e($m['contact']) ?></td><td><?= e($m['matricule']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$liste): ?><tr><td colspan="4" style="text-align:center;">Aucun membre inscrit pour le moment.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></section>
<?php
    require __DIR__ . '/includes/footer.php';
    exit;
}
redirect(LIENS_COURTS[$cible][1]);
