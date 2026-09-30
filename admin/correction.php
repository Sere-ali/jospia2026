<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$seminariste = $stmt->fetch();

if (!$seminariste) { die("Séminariste introuvable."); }
if (!$seminariste['test_complete']) { die("Ce séminariste n'a pas encore composé le test d'entrée."); }

$titrePage = "Correction - " . $seminariste['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/admin/seminariste_detail?id=<?= $seminariste['id'] ?>" class="btn btn-outline btn-sm">&larr; Retour à la fiche</a>
        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <span class="eyebrow">Correction du test</span>
            <h2>Détail des réponses - <?= e($seminariste['nom_prenoms']) ?></h2>
            <p style="color:var(--texte-doux);">Vue administrateur : détail complet, question par question, pour justifier la note attribuée.</p>
        </div>

        <?php require __DIR__ . '/../includes/correction_rendu.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
