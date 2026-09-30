<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['seminariste']);
$u = utilisateurCourant();

$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$u['seminariste_id']]);
$seminariste = $stmt->fetch();

if (!$seminariste) { die("Fiche introuvable."); }
if (!$seminariste['test_complete']) {
    redirect('/espace/fiche.php');
}

$titrePage = "Correction de mon test";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/espace/fiche.php" class="btn btn-outline btn-sm">&larr; Retour à mon espace</a>
        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <span class="eyebrow">Correction du test</span>
            <h2>Détail de mes réponses — <?= e($seminariste['nom_prenoms']) ?></h2>
            <p style="color:var(--texte-doux);">Voici, question par question, ce qui justifie votre note. Les questions en rouge sont celles où la bonne réponse n'a pas été trouvée.</p>
        </div>

        <?php require __DIR__ . '/../includes/correction_rendu.php'; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
