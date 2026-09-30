<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { die("Séminariste introuvable."); }

$titrePage = "Fiche — " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
            <button onclick="window.print()" class="btn btn-primaire">🖨️ Imprimer / Télécharger</button>
        </div>
        <?php require __DIR__ . '/../includes/astuce_impression.php'; ?>
        <div class="fiche">
            <div class="fiche-header">
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-fiche" alt="Logo">
                <div>
                    <h3 style="margin:0;">Fiche d'inscription — Séminariste</h3>
                    <div style="color:var(--texte-doux);font-size:.85rem;"><?= e(EVENT_FULL) ?></div>
                </div>
            </div>
            <?php if ($s['photo']): ?><img class="fiche-photo" src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" alt="Photo"><?php endif; ?>
            <dl>
                <dt>Matricule</dt><dd class="mono"><?= e($s['matricule']) ?></dd>
                <dt>Nom et prénoms</dt><dd><?= e($s['nom_prenoms']) ?></dd>
                <dt>Genre</dt><dd><?= e($s['genre']) ?></dd>
                <dt>Âge</dt><dd><?= e($s['age']) ?> ans</dd>
                <dt>Niveau d'études</dt><dd><?= e($s['niveau_etude']) ?></dd>
                <dt>Sous-comité</dt><dd><?= e($s['anyama']) ?></dd>
                <dt>Section</dt><dd><?= e($s['section']) ?></dd>
                <dt>Lieu de résidence</dt><dd><?= e($s['lieu_residence']) ?></dd>
                <dt>Contact</dt><dd><?= e($s['contact']) ?></dd>
                <dt>Maladie / allergie</dt><dd><?= e($s['maladie']) ?><?= $s['maladie_autre'] ? ' — ' . e($s['maladie_autre']) : '' ?></dd>
                <dt>Contact d'urgence</dt><dd><?= e($s['parent_nom']) ?> (<?= e($s['parent_lien']) ?>) — <?= e($s['parent_contact']) ?></dd>
                <?php if ($s['test_complete']): ?>
                <dt>Test d'entrée</dt><dd><?= e($s['note_test']) ?>/20 — Niveau <?= e($s['niveau_affecte']) ?></dd>
                <?php endif; ?>
            </dl>
            <div class="dortoir-box">
                <div class="label">Dortoir attribué</div>
                <div class="valeur"><?= e($s['dortoir']) ?></div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
