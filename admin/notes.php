<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY ordre, nom")->fetchAll();
$succes = null;
$erreurs = [];

$id = (int)($_GET['id'] ?? $_POST['seminariste_id'] ?? 0);
$seminaristeChoisi = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
    $stmt->execute([$id]);
    $seminaristeChoisi = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seminaristeChoisi) {
    if (!$matieres) {
        $erreurs[] = "Aucune matière n'est définie. Ajoutez-en d'abord depuis « Matières & résultats ».";
    } else {
        $upsert = $pdo->prepare("INSERT INTO notes (seminariste_id, matiere_id, note) VALUES (?,?,?)
                                  ON DUPLICATE KEY UPDATE note = VALUES(note)");
        foreach ($matieres as $m) {
            $champ = 'note_' . $m['id'];
            $valeur = $_POST[$champ] ?? '';
            $note = ($valeur === '') ? null : (float)$valeur;
            $upsert->execute([$seminaristeChoisi['id'], $m['id'], $note]);
        }
        $succes = "Notes enregistrées pour " . $seminaristeChoisi['nom_prenoms'] . ".";
    }
}

// Notes déjà saisies pour le séminariste sélectionné
$notesExistantes = [];
if ($seminaristeChoisi) {
    $stmt = $pdo->prepare("SELECT matiere_id, note FROM notes WHERE seminariste_id = ?");
    $stmt->execute([$seminaristeChoisi['id']]);
    foreach ($stmt->fetchAll() as $row) {
        $notesExistantes[$row['matiere_id']] = $row['note'];
    }
}

$recherche = trim($_GET['q'] ?? '');
$resultatsRecherche = [];
if ($recherche !== '') {
    $stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE nom_prenoms LIKE ? OR matricule LIKE ? OR contact LIKE ? ORDER BY nom_prenoms LIMIT 30");
    $like = "%$recherche%";
    $stmt->execute([$like, $like, $like]);
    $resultatsRecherche = $stmt->fetchAll();
}

$titrePage = "Saisie des notes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-tete" style="text-align:left;">
            <h2>Saisie des notes — Commission scientifique</h2>
            <p style="color:var(--texte-doux);">Recherchez un séminariste, puis renseignez sa note pour chaque matière. Les bulletins restent invisibles pour les séminaristes tant que vous n'avez pas <a href="<?= BASE_URL ?>/admin/matieres.php">publié les résultats</a>.</p>
        </div>

        <form method="get" class="carte" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
            <div class="form-group" style="flex:1;min-width:220px;margin:0;">
                <label>Rechercher un séminariste</label>
                <input type="text" name="q" value="<?= e($recherche) ?>" placeholder="Nom, matricule ou contact...">
            </div>
            <button class="btn btn-primaire">Rechercher</button>
        </form>

        <?php if ($recherche !== '' && !$seminaristeChoisi): ?>
            <div class="table-wrap" style="margin-bottom:24px;">
                <table>
                    <thead><tr><th>Matricule</th><th>Nom et prénoms</th><th>Section</th><th>Contact</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($resultatsRecherche as $r): ?>
                        <tr>
                            <td class="mono"><?= e($r['matricule']) ?></td>
                            <td><?= e($r['nom_prenoms']) ?></td>
                            <td><?= e($r['section']) ?></td>
                            <td><?= e($r['contact']) ?></td>
                            <td><a href="<?= BASE_URL ?>/admin/notes.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-primaire">Saisir ses notes</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$resultatsRecherche): ?><tr><td colspan="5">Aucun résultat.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <?php if ($seminaristeChoisi): ?>
            <div class="carte" style="max-width:640px;">
                <h3>Notes de <?= e($seminaristeChoisi['nom_prenoms']) ?> <span class="pill pill-vert"><?= e($seminaristeChoisi['matricule']) ?></span></h3>
                <?php if (!$matieres): ?>
                    <div class="alert alert-info">Aucune matière définie pour le moment.</div>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="seminariste_id" value="<?= $seminaristeChoisi['id'] ?>">
                        <?php foreach ($matieres as $m): ?>
                            <div class="form-group">
                                <label><?= e($m['nom']) ?> <span style="color:var(--texte-doux);font-weight:400;">(/ <?= e($m['note_max']) ?>)</span></label>
                                <input type="number" step="0.25" min="0" max="<?= e($m['note_max']) ?>" name="note_<?= $m['id'] ?>"
                                       value="<?= isset($notesExistantes[$m['id']]) && $notesExistantes[$m['id']] !== null ? e($notesExistantes[$m['id']]) : '' ?>"
                                       placeholder="Non noté">
                            </div>
                        <?php endforeach; ?>
                        <button class="btn btn-primaire btn-block">Enregistrer les notes</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
