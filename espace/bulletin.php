<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['seminariste']);
$u = utilisateurCourant();

$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$u['seminariste_id']]);
$seminariste = $stmt->fetch();
if (!$seminariste) { die("Fiche introuvable."); }

$publie = resultatsPublies($pdo);

$titrePage = "Mon bulletin";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <a href="<?= BASE_URL ?>/espace/fiche" class="btn btn-outline btn-sm no-print">&larr; Retour à mon espace</a>

        <?php if (!$publie): ?>
            <div class="carte" style="max-width:640px;margin:24px auto;text-align:center;">
                <h3>Bulletin non disponible</h3>
                <p style="color:var(--texte-doux);">Les résultats n'ont pas encore été publiés par le comité d'organisation. Revenez plus tard.</p>
            </div>
        <?php else: ?>
            <div class="section-tete" style="text-align:left;margin-top:16px;">
                <span class="eyebrow">Bulletin de notes</span>
                <h2><?= e($seminariste['nom_prenoms']) ?></h2>
            </div>
            <?php require __DIR__ . '/../includes/bulletin_rendu.php'; ?>
            <div style="text-align:center;margin-top:18px;" class="no-print">
                <button onclick="window.print()" class="btn btn-primaire">📄 Enregistrer en PDF</button>
            </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
