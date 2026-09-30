<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s || !$s['test_complete']) { die("Diplôme indisponible (test d'entrée non complété)."); }

$titrePage = "Diplôme — " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <button onclick="window.print()" class="btn btn-primaire">🖨️ Imprimer / Télécharger le diplôme</button>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <div class="diplome-card">
            <div class="mention">Certificat de participation</div>
            <h1><?= e(EVENT_FULL) ?></h1>
            <div>Ce diplôme est décerné à</div>
            <div class="nom-diplome"><?= e($s['nom_prenoms']) ?></div>
            <p class="texte">pour sa participation active aux <?= e(EVENT_NAME) ?>, ayant obtenu la note de <strong><?= e($s['note_test']) ?>/20</strong> au test d'entrée et atteint le niveau <strong><?= e($s['niveau_affecte']) ?></strong>.</p>
            <div class="signatures"><span>Le Coordonnateur</span><span>Le Super Administrateur</span></div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
