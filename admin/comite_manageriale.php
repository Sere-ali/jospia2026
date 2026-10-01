<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
$titrePage = "Comité managérial";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
$cartes = [
    ['🚪 Sorties MG / MGA', 'Accepter ou refuser les demandes de sortie du camp.', '/espace/sorties_mg'],
    ['📝 Rapports journaliers', 'Rapport du jour de chaque commission.', '/admin/rapports'],
];
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Comité managérial</span><h2>Comité managérial</h2></div>
        <div class="grid grid-3">
            <?php foreach ($cartes as [$titre, $desc, $lien]): ?>
                <div class="carte"><h3><?= $titre ?></h3><p><?= e($desc) ?></p><a href="<?= BASE_URL . $lien ?>" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
