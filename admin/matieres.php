<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);

$erreurs = [];
$succes = null;

// Publier / dépublier les résultats
if (isset($_GET['publier'])) {
    $valeur = $_GET['publier'] === '1' ? '1' : '0';
    $pdo->prepare("UPDATE parametres SET valeur = ? WHERE cle = 'resultats_publies'")->execute([$valeur]);
    redirect('/admin/matieres');
}

if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM matieres WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    redirect('/admin/matieres');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $noteMax = (float)($_POST['note_max'] ?? 20);
    $ordre = (int)($_POST['ordre'] ?? 0);

    if ($nom === '' || $noteMax <= 0) {
        $erreurs[] = "Le nom et le barème (note maximale) sont obligatoires.";
    } else {
        if ($id > 0) {
            $pdo->prepare("UPDATE matieres SET nom=?, note_max=?, ordre=? WHERE id=?")->execute([$nom, $noteMax, $ordre, $id]);
            $succes = "Matière modifiée.";
        } else {
            $pdo->prepare("INSERT INTO matieres (nom, note_max, ordre) VALUES (?,?,?)")->execute([$nom, $noteMax, $ordre]);
            $succes = "Matière ajoutée.";
        }
    }
}

$matiereEnEdition = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("SELECT * FROM matieres WHERE id = ?");
    $stmt->execute([(int)$_GET['modifier']]);
    $matiereEnEdition = $stmt->fetch();
}

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY ordre, nom")->fetchAll();
$publie = resultatsPublies($pdo);

$titrePage = "Matières & résultats";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-tete" style="text-align:left;">
            <h2>Matières du bulletin & publication des résultats</h2>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="carte" style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
            <div>
                <h3 style="margin-bottom:4px;">Statut des résultats</h3>
                <p style="margin:0;color:var(--texte-doux);">
                    <?php if ($publie): ?>
                        <span class="pill pill-vert">✅ Résultats publiés</span> - les séminaristes voient leur bulletin sur leur espace personnel.
                    <?php else: ?>
                        <span class="pill pill-gris">🔒 Résultats non publiés</span> - les bulletins restent invisibles pour les séminaristes.
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($publie): ?>
                <a href="<?= BASE_URL ?>/admin/matieres?publier=0" class="btn btn-danger" onclick="return confirm('Masquer les bulletins aux séminaristes ?')">Dépublier les résultats</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/admin/matieres?publier=1" class="btn btn-primaire" onclick="return confirm('Rendre les bulletins visibles à tous les séminaristes ?')">Publier les résultats</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/bulletins_impression" class="btn btn-outline">📄 Bulletins en PDF (2/feuille A4 paysage)</a>
        </div>

        <div class="carte" style="margin-bottom:24px;">
            <h3><?= $matiereEnEdition ? 'Modifier la matière' : 'Ajouter une matière / composition' ?></h3>
            <form method="post">
                <input type="hidden" name="id" value="<?= $matiereEnEdition['id'] ?? '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom de la matière</label>
                        <input type="text" name="nom" required placeholder="Ex : Fiqh, Tajwid, Culture générale..." value="<?= e($matiereEnEdition['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Note maximale (barème)</label>
                        <input type="number" step="0.5" name="note_max" required value="<?= e($matiereEnEdition['note_max'] ?? '20') ?>">
                    </div>
                </div>
                <div class="form-group" style="max-width:200px;">
                    <label>Ordre d'affichage</label>
                    <input type="number" name="ordre" value="<?= e($matiereEnEdition['ordre'] ?? count($matieres) + 1) ?>">
                </div>
                <button class="btn btn-primaire"><?= $matiereEnEdition ? 'Enregistrer' : 'Ajouter la matière' ?></button>
                <?php if ($matiereEnEdition): ?><a href="<?= BASE_URL ?>/admin/matieres" class="btn btn-outline">Annuler</a><?php endif; ?>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Ordre</th><th>Matière</th><th>Barème</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($matieres as $m): ?>
                    <tr>
                        <td><?= e($m['ordre']) ?></td>
                        <td><?= e($m['nom']) ?></td>
                        <td>/ <?= e($m['note_max']) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/matieres?modifier=<?= $m['id'] ?>" class="btn btn-sm btn-outline">✏️</a>
                            <a href="<?= BASE_URL ?>/admin/matieres?supprimer=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette matière ? Les notes associées seront aussi supprimées.')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$matieres): ?><tr><td colspan="4">Aucune matière définie.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
