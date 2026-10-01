<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);
$titrePage = "Commission scientifique";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Commission scientifique</span>
            <h2>Notes, bulletins et test d'entrée</h2>
        </div>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>
        <?php if (estSuperAdmin()) echo blocTestEntree($pdo); ?>
        <div class="grid grid-2">
            <div class="carte"><h3>📝 Saisie des notes</h3><p>Saisir les notes des séminaristes par matière.</p><a href="<?= BASE_URL ?>/admin/notes" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <div class="carte"><h3>🖨️ Bulletins</h3><p>Télécharger les bulletins en PDF (2 par page).</p><a href="<?= BASE_URL ?>/admin/bulletins_impression" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <div class="carte"><h3>🧪 Test d'entrée</h3><p>Verrouiller / déverrouiller le test, suivre les résultats (durée et notation sur 20).</p><a href="<?= BASE_URL ?>/admin/test_entree" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <div class="carte"><h3>📋 Listes par dortoir et par niveau</h3><p>Consulter et exporter en Excel les listes des séminaristes.</p><a href="<?= BASE_URL ?>/admin/listes" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <?php if (estScientifique()): ?>
            <div class="carte"><h3>📚 Matières et résultats</h3><p>Gérer les matières et publier les résultats.</p><a href="<?= BASE_URL ?>/admin/matieres" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <div class="carte"><h3>📝 Questions du test</h3><p>Ajouter ou modifier les questions du test d'entrée.</p><a href="<?= BASE_URL ?>/admin/questions" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <div class="carte"><h3>⚙️ Config Quiz (6 banques)</h3><p>Régler le tirage des questions par banque.</p><a href="<?= BASE_URL ?>/admin/config_quiz" class="btn btn-primaire btn-sm">Ouvrir</a></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
