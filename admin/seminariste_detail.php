<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { die("Séminariste introuvable."); }

$titrePage = "Détail — " . $s['nom_prenoms'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/admin/seminaristes.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>
        <div class="carte" style="margin-top:16px;">
            <?php if ($s['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" style="width:110px;height:130px;object-fit:cover;border-radius:8px;float:right;margin-left:16px;border:2px solid var(--vert);"><?php endif; ?>
            <h2><?= e($s['nom_prenoms']) ?></h2>
            <p><span class="pill pill-vert"><?= e($s['anyama']) ?> — <?= e($s['section']) ?></span> <span class="pill pill-gris"><?= e($s['dortoir']) ?></span>
               <?php if ($s['test_complete']): ?><span class="pill pill-or"><?= e($s['niveau_affecte']) ?> — <?= e($s['note_test']) ?>/20</span><?php endif; ?></p>

            <h3>Identité</h3>
            <p><strong>Matricule :</strong> <span class="mono"><?= e($s['matricule']) ?></span><br>
               <strong>Genre :</strong> <?= e($s['genre']) ?> — <strong>Âge :</strong> <?= e($s['age']) ?> ans<br>
               <strong>Niveau d'études :</strong> <?= e($s['niveau_etude']) ?><br>
               <strong>Lieu de résidence :</strong> <?= e($s['lieu_residence']) ?><br>
               <strong>Contact :</strong> <?= e($s['contact']) ?></p>

            <h3>Sous-comité</h3>
            <p><strong>Sous-comité :</strong> <?= e($s['anyama']) ?><br>
               <strong>Section :</strong> <?= e($s['section']) ?></p>

            <h3>Santé</h3>
            <p><?= e($s['maladie']) ?><?= $s['maladie_autre'] ? ' — ' . e($s['maladie_autre']) : '' ?></p>

            <h3>Contact d'urgence</h3>
            <p><?= e($s['parent_nom']) ?> (<?= e($s['parent_lien']) ?>) — <?= e($s['parent_contact']) ?></p>

            <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/admin/download_fiche.php?id=<?= $s['id'] ?>" class="btn btn-primaire">📄 Fiche d'inscription</a>
                <a href="<?= BASE_URL ?>/admin/download_badge_seminariste.php?id=<?= $s['id'] ?>" class="btn btn-or">🪪 Badge</a>
                <?php if ($s['test_complete']): ?>
                    <a href="<?= BASE_URL ?>/admin/download_diplome.php?id=<?= $s['id'] ?>" class="btn btn-or">🎖️ Diplôme</a>
                    <a href="<?= BASE_URL ?>/admin/correction.php?id=<?= $s['id'] ?>" class="btn btn-outline">🔍 Correction du test</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/admin/notes.php?id=<?= $s['id'] ?>" class="btn btn-outline">📝 Saisir ses notes</a>
                <a href="<?= BASE_URL ?>/admin/bulletin.php?id=<?= $s['id'] ?>" class="btn btn-outline">📄 Voir son bulletin</a>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
