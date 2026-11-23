<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin', 'mg']);
$titrePage = "Comité managérial";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
$cartes = [
    ['Sorties MG / MGA', 'Accepter ou refuser les demandes de sortie du camp.', '/espace/sorties_mg'],
    ['Présentation du lancement', 'Grandes activités et innovations de JOSPIA 2026 — présentation animée du 29 novembre.', '/admin/presentation_lancement'],
    ['Présentation des responsables', 'Présentation animée des responsables de commission, construite à partir de leurs fiches.', '/admin/presentation_responsables', ['Fiches et lien du formulaire', '/admin/responsables_inscriptions']],
    ['Rapports journaliers', 'Rapport du jour de chaque commission.', '/admin/rapports'],
];
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Comité managérial</span><h2>Comité managérial</h2></div>
        <div class="grid grid-3">
            <?php foreach ($cartes as $cc): [$titre, $desc, $lien] = $cc; $second = $cc[3] ?? null; ?>
                <div class="carte"><h3><?= $titre ?></h3><p><?= e($desc) ?></p><a href="<?= BASE_URL . $lien ?>" class="btn btn-primaire btn-sm">Ouvrir</a><?php if (!empty($second)): ?> <a href="<?= BASE_URL . $second[1] ?>" class="btn btn-outline btn-sm"><?= e($second[0]) ?></a><?php endif; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
