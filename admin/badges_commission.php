<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$filtreCommission = $_GET['commission'] ?? '';
$sql = "SELECT * FROM membres_commission WHERE 1=1";
$params = [];
if ($filtreCommission !== '') {
    $sql .= " AND commission = ?";
    $params[] = $filtreCommission;
}
$sql .= " ORDER BY commission, nom_prenoms";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$membres = $stmt->fetchAll();
$pages = array_chunk($membres, 4);

$titrePage = "Impression des badges - Commission";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/commissions" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <form method="get" style="display:inline-flex;gap:8px;">
                <select name="commission" onchange="this.form.submit()">
                    <option value="">Toutes les commissions (<?= count($membres) ?> badges)</option>
                    <?php foreach (listeCommissions() as $c): ?>
                        <option value="<?= e($c) ?>" <?= $filtreCommission === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="<?= BASE_URL ?>/admin/pdf?type=badge_com&tous=1&commission=<?= urlencode($filtreCommission) ?>" class="btn btn-primaire">⬇️ Télécharger <?= count($membres) ?> badge(s) en PDF - <?= count($pages) ?> page(s) A4</a>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>

        <?php if (!$membres): ?>
            <div class="alert alert-info">Aucun badge à imprimer.</div>
        <?php endif; ?>

        <?php foreach ($pages as $page): ?>
            <div class="page-badges">
                <?php foreach ($page as $membre): ?>
                    <?php require __DIR__ . '/../includes/badge_commission_carte.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
