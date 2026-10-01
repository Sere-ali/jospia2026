<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
$doc = ($_GET['doc'] ?? 'badges') === 'diplomes' ? 'diplomes' : 'badges';
$groupe = ($_GET['groupe'] ?? 'sem') === 'com' ? 'com' : 'sem';
$valeur = trim($_GET['valeur'] ?? '');
$estBadge = ($doc === 'badges');

if ($groupe === 'sem') {
    $w = '1=1'; $p = [];
    if ($valeur === 'none') { $w .= " AND (niveau_affecte IS NULL OR niveau_affecte = '')"; }
    elseif ($valeur !== '') { $w .= ' AND niveau_affecte = ?'; $p[] = $valeur; }
    $st = $pdo->prepare("SELECT * FROM seminaristes WHERE $w ORDER BY dortoir, nom_prenoms");
    $titreGroupe = $valeur === '' ? 'Séminaristes - tous les niveaux' : ($valeur === 'none' ? 'Séminaristes - niveau non affecté' : 'Séminaristes - ' . $valeur);
} else {
    $w = '1=1'; $p = [];
    if ($valeur !== '') { $w .= ' AND commission = ?'; $p[] = $valeur; }
    $st = $pdo->prepare("SELECT * FROM membres_commission WHERE $w ORDER BY commission, nom_prenoms");
    $titreGroupe = $valeur === '' ? 'Membres - toutes les commissions' : 'Commission ' . $valeur;
}
$st->execute($p);
$lignes = $st->fetchAll();
$total = count($lignes);

$parPage = $estBadge ? 12 : 4;
$nbPages = max(1, (int)ceil($total / $parPage));
$page = min($nbPages, max(1, (int)($_GET['page'] ?? 1)));
$affiche = array_slice($lignes, ($page - 1) * $parPage, $parPage);

$qsPdf = http_build_query(array_filter([
    'type' => ($estBadge ? 'badge_' : 'diplome_') . $groupe, 'tous' => 1,
    ($groupe === 'sem' ? 'niveau' : 'commission') => $valeur,
], function ($v) { return $v !== ''; }));
$base = BASE_URL . '/admin/apercu_documents?' . http_build_query(['doc' => $doc, 'groupe' => $groupe, 'valeur' => $valeur]);

$titrePage = ($estBadge ? 'Badges' : 'Diplômes') . ' - ' . $titreGroupe;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section<?= $estBadge ? '' : ' jos-cert-print' ?>">
    <div class="container">
        <div class="section-titre" style="text-align:left;">
            <span class="eyebrow"><?= $estBadge ? 'Badges' : 'Diplômes' ?></span>
            <h2><?= e($titreGroupe) ?></h2>
            <p><?= $total ?> <?= $estBadge ? 'badge' : 'diplôme' ?><?= $total > 1 ? 's' : '' ?> - aperçu avant téléchargement.</p>
        </div>
        <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
            <?php if ($total): ?><a href="<?= BASE_URL ?>/admin/pdf?<?= e($qsPdf) ?>" class="btn btn-primaire">⬇️ Télécharger <?= $total > 1 ? 'les ' . $total . ' ' . ($estBadge ? 'badges' : 'diplômes') : ($estBadge ? 'le badge' : 'le diplôme') ?> en PDF</a><?php endif; ?>
        </div>
        <?php if (!$total): ?><div class="alert alert-info">Aucun document dans cette catégorie.</div><?php endif; ?>

        <?php if ($estBadge): ?>
            <div class="page-badges">
                <?php foreach ($affiche as $l): ?>
                    <?php if ($groupe === 'sem') { $s = $l; require __DIR__ . '/../includes/badge_seminariste_carte.php'; }
                          else { $membre = $l; require __DIR__ . '/../includes/badge_commission_carte.php'; } ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <?php foreach ($affiche as $l): ?>
                <div style="margin-bottom:22px;">
                    <?php if ($groupe === 'sem') { $nomCertificat = $l['nom_prenoms']; require __DIR__ . '/../includes/certificat_seminariste_carte.php'; }
                          else { $nomCertificat = $l['nom_prenoms']; $qualiteCertificat = 'MEMBRE DE LA COMMISSION ' . nomCommissionComplet($l['commission']); require __DIR__ . '/../includes/certificat_carte.php'; } ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($nbPages > 1): ?>
            <div class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;align-items:center;margin-top:18px;">
                <?php if ($page > 1): ?><a class="btn btn-outline btn-sm" href="<?= e($base . '&page=' . ($page - 1)) ?>">&larr; Précédent</a><?php endif; ?>
                <span>Page <?= $page ?> / <?= $nbPages ?></span>
                <?php if ($page < $nbPages): ?><a class="btn btn-outline btn-sm" href="<?= e($base . '&page=' . ($page + 1)) ?>">Suivant &rarr;</a><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
