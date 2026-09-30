<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$seminariste = $stmt->fetch();
if (!$seminariste) { die("Séminariste introuvable."); }

$titrePage = "Bulletin - " . $seminariste['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/seminariste_detail?id=<?= $seminariste['id'] ?>" class="btn btn-outline btn-sm">&larr; Retour à la fiche</a>
            <a href="<?= BASE_URL ?>/admin/notes?id=<?= $seminariste['id'] ?>" class="btn btn-outline btn-sm">✏️ Modifier les notes</a>
            <button onclick="window.print()" class="btn btn-primaire">🖨️ Imprimer / Télécharger le bulletin</button>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <?php require __DIR__ . '/../includes/bulletin_rendu.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
