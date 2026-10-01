<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
$titrePage = "Administration";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
$cartes = [
    ['👥 Membres commission', 'Liste, badges et diplômes des membres de commission.', '/admin/commissions'],
    ['🎓 Séminaristes', 'Liste, fiches, badges et diplômes des séminaristes.', '/admin/seminaristes'],
    ['🪪 Badges', 'Badges par niveau (séminaristes) et par commission, en PDF.', '/admin/badges'],
    ['🎓 Diplômes', 'Diplômes par niveau et par commission, en PDF.', '/admin/diplomes'],
    ['🔑 Identifiants', 'Retrouver un identifiant ou réinitialiser un mot de passe.', '/admin/identifiants'],
    ['🛏️ Dortoirs', 'Occupation et capacité des dortoirs.', '/admin/dortoirs'],
    ['📋 Listes dortoir/niveau', 'Listes nominatives par dortoir et par niveau (export Excel).', '/admin/listes'],
    ['🚪 Sorties - MG / MGA', 'Accepter ou refuser les demandes de sortie du camp.', '/espace/sorties_mg'],
];
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Administration</span><h2>Administration</h2></div>
        <div class="grid grid-3">
            <?php foreach ($cartes as [$titre, $desc, $lien]): ?>
                <div class="carte"><h3><?= $titre ?></h3><p><?= e($desc) ?></p><a href="<?= BASE_URL . $lien ?>" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
