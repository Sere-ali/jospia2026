<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM membres_commission WHERE id = ?");
$stmt->execute([$id]);
$membre = $stmt->fetch();
if (!$membre) { die("Membre introuvable."); }

$titrePage = "Diplôme - " . $membre['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section jos-cert-print">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/commissions" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <a href="<?= BASE_URL ?>/admin/pdf?type=diplome_com&id=<?= (int)$membre['id'] ?>" class="btn btn-primaire">⬇️ Télécharger le diplôme en PDF (A4)</a>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <?php $nomCertificat = $membre['nom_prenoms']; $qualiteCertificat = 'MEMBRE DE LA COMMISSION ' . $membre['commission']; require __DIR__ . '/../includes/certificat_carte.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
