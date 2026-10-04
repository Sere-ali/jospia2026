<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    if (estSuperAdmin()) {
        $idS = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM comptes WHERE seminariste_id = ?")->execute([$idS]);
        $pdo->prepare("DELETE FROM seminaristes WHERE id = ?")->execute([$idS]);
        $_SESSION['flash_succes'] = "Séminariste supprimé.";
    }
    redirect('/admin/listes');
}

// Groupement par dortoir (y compris "Pépinière")
$parDortoir = [];
$stmt = $pdo->query("SELECT * FROM seminaristes ORDER BY dortoir, nom_prenoms");
foreach ($stmt->fetchAll() as $s) {
    $cle = $s['dortoir'] ?: 'Non affecté';
    $parDortoir[$cle][] = $s;
}
// Ordonner : dortoirs Hommes, puis Femmes, puis Pépinière, puis autres
$ordreDortoirs = array_merge(DORTOIRS_FRERES, DORTOIRS_SOEURS, ['Pépinière']);
uksort($parDortoir, function($a, $b) use ($ordreDortoirs) {
    $ia = array_search($a, $ordreDortoirs); $ia = $ia === false ? 99 : $ia;
    $ib = array_search($b, $ordreDortoirs); $ib = $ib === false ? 99 : $ib;
    return $ia <=> $ib;
});

// Groupement par niveau
$parNiveau = [];
$stmt2 = $pdo->query("SELECT * FROM seminaristes ORDER BY niveau_affecte, nom_prenoms");
foreach ($stmt2->fetchAll() as $s) {
    $cle = $s['niveau_affecte'] ?: 'Test non composé';
    $parNiveau[$cle][] = $s;
}
$ordreNiveaux = array_merge(listeNiveaux(), ['Test non composé']);
uksort($parNiveau, function($a, $b) use ($ordreNiveaux) {
    $ia = array_search($a, $ordreNiveaux); $ia = $ia === false ? 99 : $ia;
    $ib = array_search($b, $ordreNiveaux); $ib = $ib === false ? 99 : $ib;
    return $ia <=> $ib;
});

$titrePage = "Listes par dortoir et par niveau";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-tete" style="text-align:left;">
            <h2>Listes des séminaristes</h2>
        </div>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>

        <div data-onglets style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;" class="no-print">
            <button type="button" class="btn btn-primaire btn-sm actif" data-onglet="dortoir">🛏️ Par dortoir</button>
            <button type="button" class="btn btn-outline btn-sm" data-onglet="niveau">🎓 Par niveau</button>
            <button type="button" onclick="window.print()" class="btn btn-outline btn-sm" style="margin-left:auto;">🖨️ Imprimer cette liste</button>
        </div>

        <div data-panneau="dortoir">
            <?php foreach ($parDortoir as $nomDortoir => $liste): ?>
                <div class="carte" style="margin-bottom:18px;">
                    <h3><?= e($nomDortoir) ?> <span class="pill pill-vert"><?= count($liste) ?></span></h3>
                    <div class="table-wrap" style="box-shadow:none;">
                        <table>
                            <thead><tr><th>Matricule</th><th>Nom et prénoms</th><th>Genre</th><th>Âge</th><th>Section</th><th>Contact</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($liste as $s): ?>
                                <tr>
                                    <td class="mono"><?= e($s['matricule']) ?></td>
                                    <td><?= e($s['nom_prenoms']) ?></td>
                                    <td><?= e($s['genre']) ?></td>
                                    <td><?= e($s['age']) ?></td>
                                    <td><?= e($s['section']) ?></td>
                                    <td><?= e($s['contact']) ?></td>
                                    <td class="no-print" style="white-space:nowrap;">
                                        <?php if (estAdmin()): ?>
                                        <a href="<?= BASE_URL ?>/admin/edit_seminariste?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                                        <?php endif; ?>
                                        <?php if (estSuperAdmin()): ?>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer définitivement <?= e(addslashes($s['nom_prenoms'])) ?> ?');">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$parDortoir): ?><div class="alert alert-info">Aucun séminariste inscrit pour le moment.</div><?php endif; ?>
        </div>

        <div data-panneau="niveau" style="display:none;">
            <?php foreach ($parNiveau as $nomNiveau => $liste): ?>
                <div class="carte" style="margin-bottom:18px;">
                    <h3><?= e($nomNiveau) ?> <span class="pill pill-or"><?= count($liste) ?></span></h3>
                    <div class="table-wrap" style="box-shadow:none;">
                        <table>
                            <thead><tr><th>Matricule</th><th>Nom et prénoms</th><th>Note /20</th><th>Section</th><th>Dortoir</th><th>Contact</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($liste as $s): ?>
                                <tr>
                                    <td class="mono"><?= e($s['matricule']) ?></td>
                                    <td><?= e($s['nom_prenoms']) ?></td>
                                    <td><?= $s['test_complete'] ? e($s['note_test']) : '-' ?></td>
                                    <td><?= e($s['section']) ?></td>
                                    <td><?= e($s['dortoir']) ?></td>
                                    <td><?= e($s['contact']) ?></td>
                                    <td class="no-print" style="white-space:nowrap;">
                                        <?php if (estAdmin()): ?>
                                        <a href="<?= BASE_URL ?>/admin/edit_seminariste?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                                        <?php endif; ?>
                                        <?php if (estSuperAdmin()): ?>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer définitivement <?= e(addslashes($s['nom_prenoms'])) ?> ?');">
                                            <input type="hidden" name="action" value="supprimer">
                                            <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$parNiveau): ?><div class="alert alert-info">Aucun séminariste inscrit pour le moment.</div><?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
