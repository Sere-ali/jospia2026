<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { die("Séminariste introuvable."); }

$titrePage = "Diplôme - " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section jos-cert-print">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/seminaristes" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <a href="<?= BASE_URL ?>/admin/pdf?type=diplome_sem&id=<?= (int)$s['id'] ?>" class="btn btn-primaire">⬇️ Télécharger le diplôme en PDF (A4)</a>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <?php $nomCertificat = $s['nom_prenoms']; require __DIR__ . '/../includes/certificat_seminariste_carte.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
