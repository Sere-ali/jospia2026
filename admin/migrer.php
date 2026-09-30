<?php
/**
 * Applique la migration SQL (paiements, banques de questions).
 * Réservé au Super Admin. Idempotent : sans danger si relancé.
 */
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);

$message = null;
$ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->exec(file_get_contents(__DIR__ . '/../sql/migration_features.sql'));
        $ok = true;
        $message = "Migration exécutée avec succès.";
    } catch (Throwable $e) {
        $message = "Erreur : " . $e->getMessage();
    }
}
$titrePage = "Migration de la base";
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container" style="max-width:640px;margin:40px auto;">
    <h2>Migration de la base de données</h2>
    <p>Ajoute les tables et colonnes manquantes (paiements Wave, banques de questions). Sans risque si déjà appliquée.</p>
    <?php if ($message): ?><div class="alert <?= $ok ? 'alert-succes' : 'alert-erreur' ?>"><?= e($message) ?></div><?php endif; ?>
    <form method="post"><button type="submit" class="btn btn-primaire">Lancer la migration</button></form>
    <p style="margin-top:16px;"><a href="<?= BASE_URL ?>/admin/dashboard.php">Aller au tableau de bord</a></p>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
