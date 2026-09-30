<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { die("Séminariste introuvable."); }

$titrePage = "Badge — " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <button onclick="window.print()" class="btn btn-primaire">🖨️ Imprimer / Télécharger le badge</button>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <?php require __DIR__ . '/../includes/badge_seminariste_carte.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
