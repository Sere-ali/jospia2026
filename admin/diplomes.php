<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
$MODE = 'diplomes'; // badges | diplomes
$estBadge = ($MODE === 'badges');
$niveaux = ['Pépinière', 'Primaire', 'Secondaire', 'Universitaire', 'Leader'];

// Effectifs par niveau (séminaristes) et par commission (membres)
$condSem = $estBadge ? '1=1' : "(test_complete = 1 OR niveau_affecte = 'Pépinière')";
$parNiveau = [];
foreach ($pdo->query("SELECT COALESCE(NULLIF(niveau_affecte,''),'none') n, COUNT(*) c FROM seminaristes WHERE $condSem GROUP BY n")->fetchAll() as $r) { $parNiveau[$r['n']] = (int)$r['c']; }
$parCom = [];
foreach ($pdo->query("SELECT commission, COUNT(*) c FROM membres_commission GROUP BY commission")->fetchAll() as $r) { $parCom[$r['commission']] = (int)$r['c']; }
$totalSem = array_sum($parNiveau); $totalCom = array_sum($parCom);
$typeSem = $estBadge ? 'badge_sem' : 'diplome_sem';
$typeCom = $estBadge ? 'badge_com' : 'diplome_com';
$mot = $estBadge ? 'badge' : 'diplôme';

$titrePage = $estBadge ? "Badges" : "Diplômes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Back-office</span>
            <h2><?= $estBadge ? '🪪 Badges' : '🎓 Diplômes' ?></h2>
            <p><?= $estBadge ? 'Tous les badges, classés par niveau (séminaristes) et par commission (membres). 4 badges par page A4.' : 'Tous les diplômes, classés par niveau (séminaristes) et par commission (membres). Un diplôme par page A4.' ?> « Voir » affiche les documents sur le site avant de les télécharger en PDF.</p>
        </div>

        <h3 style="margin:0 0 12px;">🎓 Séminaristes par niveau</h3>
        <div data-live="sem" class="grid grid-3" style="margin-bottom:34px;">
            <?php foreach ($niveaux as $n): $c = $parNiveau[$n] ?? 0; ?>
                <div class="carte">
                    <h3><?= e($n) ?></h3>
                    <p><strong><?= $c ?></strong> <?= $mot ?><?= $c > 1 ? 's' : '' ?></p>
                    <?php if ($c): ?><a href="<?= BASE_URL ?>/admin/apercu_documents?doc=<?= $MODE ?>&groupe=sem&valeur=<?= urlencode($n) ?>" class="btn btn-primaire btn-sm">👁️ Voir</a> <a href="<?= BASE_URL ?>/admin/pdf?type=<?= $typeSem ?>&tous=1&niveau=<?= urlencode($n) ?>" class="btn btn-outline btn-sm">⬇️ PDF</a>
                    <?php else: ?><span class="pill pill-gris">Aucun</span><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if ($estBadge && !empty($parNiveau['none'])): ?>
                <div class="carte"><h3>Niveau non affecté</h3><p><strong><?= $parNiveau['none'] ?></strong> badge(s)</p>
                    <a href="<?= BASE_URL ?>/admin/apercu_documents?doc=badges&groupe=sem&valeur=none" class="btn btn-primaire btn-sm">👁️ Voir</a> <a href="<?= BASE_URL ?>/admin/pdf?type=badge_sem&tous=1&niveau=none" class="btn btn-outline btn-sm">⬇️ PDF</a></div>
            <?php endif; ?>
            <div class="carte" style="border-left:4px solid var(--orange);">
                <h3>Tous les niveaux</h3>
                <p><strong><?= $totalSem ?></strong> <?= $mot ?><?= $totalSem > 1 ? 's' : '' ?></p>
                <?php if ($totalSem): ?><a href="<?= BASE_URL ?>/admin/apercu_documents?doc=<?= $MODE ?>&groupe=sem" class="btn btn-or btn-sm">👁️ Voir</a> <a href="<?= BASE_URL ?>/admin/pdf?type=<?= $typeSem ?>&tous=1" class="btn btn-outline btn-sm">⬇️ Tout en PDF</a><?php endif; ?>
            </div>
        </div>

        <h3 style="margin:0 0 12px;">👥 Membres par commission</h3>
        <div data-live="com" class="grid grid-3">
            <?php foreach (listeCommissions() as $com): $c = $parCom[$com] ?? 0; ?>
                <div class="carte">
                    <h3><?= e($com) ?></h3>
                    <p><strong><?= $c ?></strong> <?= $mot ?><?= $c > 1 ? 's' : '' ?></p>
                    <?php if ($c): ?><a href="<?= BASE_URL ?>/admin/apercu_documents?doc=<?= $MODE ?>&groupe=com&valeur=<?= urlencode($com) ?>" class="btn btn-primaire btn-sm">👁️ Voir</a> <a href="<?= BASE_URL ?>/admin/pdf?type=<?= $typeCom ?>&tous=1&commission=<?= urlencode($com) ?>" class="btn btn-outline btn-sm">⬇️ PDF</a>
                    <?php else: ?><span class="pill pill-gris">Aucun</span><?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="carte" style="border-left:4px solid var(--orange);">
                <h3>Toutes les commissions</h3>
                <p><strong><?= $totalCom ?></strong> <?= $mot ?><?= $totalCom > 1 ? 's' : '' ?></p>
                <?php if ($totalCom): ?><a href="<?= BASE_URL ?>/admin/apercu_documents?doc=<?= $MODE ?>&groupe=com" class="btn btn-or btn-sm">👁️ Voir</a> <a href="<?= BASE_URL ?>/admin/pdf?type=<?= $typeCom ?>&tous=1" class="btn btn-outline btn-sm">⬇️ Tout en PDF</a><?php endif; ?>
            </div>
        </div>
        <p style="color:var(--texte-doux);margin-top:18px;">Avec beaucoup de documents, la génération du PDF peut prendre un peu de temps : patientez après avoir cliqué.</p>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
