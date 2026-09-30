<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

if (isset($_GET['supprimer']) && estSuperAdmin()) {
    $pdo->prepare("DELETE FROM comptes WHERE seminariste_id = ?")->execute([(int)$_GET['supprimer']]);
    $pdo->prepare("DELETE FROM seminaristes WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    redirect('/admin/seminaristes');
}

$recherche = trim($_GET['q'] ?? '');
$filtreGenre = $_GET['genre'] ?? '';
$filtreDortoir = $_GET['dortoir'] ?? '';
$filtreNiveau = $_GET['niveau'] ?? '';

$sql = "SELECT * FROM seminaristes WHERE 1=1";
$params = [];
if ($recherche !== '') {
    $sql .= " AND (nom_prenoms LIKE ? OR matricule LIKE ? OR contact LIKE ?)";
    $params[] = "%$recherche%"; $params[] = "%$recherche%"; $params[] = "%$recherche%";
}
if ($filtreGenre !== '') { $sql .= " AND genre = ?"; $params[] = $filtreGenre; }
if ($filtreDortoir !== '') { $sql .= " AND dortoir = ?"; $params[] = $filtreDortoir; }
if ($filtreNiveau !== '') { $sql .= " AND niveau_affecte = ?"; $params[] = $filtreNiveau; }
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$seminaristes = $stmt->fetchAll();

$dortoirsListe = $pdo->query("SELECT nom FROM dortoirs ORDER BY nom")->fetchAll(PDO::FETCH_COLUMN);

$titrePage = "Séminaristes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h2>Séminaristes (<?= count($seminaristes) ?>)</h2>
            <a href="<?= BASE_URL ?>/admin/badges_seminaristes" class="btn btn-or btn-sm">⬇️ Télécharger tous les badges en PDF (4/page)</a>
        </div>

        <form method="get" class="carte" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
            <div class="form-group" style="flex:1;min-width:180px;margin:0;">
                <label>Rechercher</label>
                <input type="text" name="q" value="<?= e($recherche) ?>" placeholder="Nom, matricule, contact...">
            </div>
            <div class="form-group" style="min-width:150px;margin:0;">
                <label>Genre</label>
                <select name="genre">
                    <option value="">Tous</option>
                    <option value="Masculin" <?= $filtreGenre==='Masculin'?'selected':'' ?>>Masculin</option>
                    <option value="Féminin" <?= $filtreGenre==='Féminin'?'selected':'' ?>>Féminin</option>
                </select>
            </div>
            <div class="form-group" style="min-width:170px;margin:0;">
                <label>Dortoir</label>
                <select name="dortoir">
                    <option value="">Tous</option>
                    <?php foreach ($dortoirsListe as $d): ?>
                        <option value="<?= e($d) ?>" <?= $filtreDortoir===$d?'selected':'' ?>><?= e($d) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="min-width:170px;margin:0;">
                <label>Niveau</label>
                <select name="niveau">
                    <option value="">Tous</option>
                    <?php foreach (['Pépinière','Primaire','Secondaire','Universitaire','Leader'] as $n): ?>
                        <option value="<?= $n ?>" <?= $filtreNiveau===$n?'selected':'' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primaire">Filtrer</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Photo</th><th>Matricule</th><th>Nom et prénoms</th><th>Genre</th><th>Âge</th><th>Section</th><th>Dortoir</th><th>Test</th><th>Niveau</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($seminaristes as $s): ?>
                    <tr>
                        <td><?php if ($s['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                        <td class="mono"><?= e($s['matricule']) ?></td>
                        <td><?= e($s['nom_prenoms']) ?></td>
                        <td><?= e($s['genre']) ?></td>
                        <td><?= e($s['age']) ?></td>
                        <td><span class="pill pill-vert"><?= e($s['section']) ?></span></td>
                        <td><?= e($s['dortoir']) ?></td>
                        <td><?php if ($s['test_complete']): ?><span class="pill pill-or"><?= e($s['note_test']) ?>/20</span><?php else: ?><span class="pill pill-gris">En attente</span><?php endif; ?></td>
                        <td><?= e($s['niveau_affecte'] ?? '-') ?></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/edit_seminariste?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <a href="<?= BASE_URL ?>/admin/identifiants?q=<?= urlencode($s['matricule']) ?>" class="btn btn-sm btn-outline">🔑 Identifiant</a>
                            <a href="<?= BASE_URL ?>/admin/seminariste_detail?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">👁️</a>
                            <a href="<?= BASE_URL ?>/admin/download_fiche?id=<?= $s['id'] ?>" class="btn btn-sm btn-primaire">📄 Fiche</a>
                            <a href="<?= BASE_URL ?>/admin/download_badge_seminariste?id=<?= $s['id'] ?>" class="btn btn-sm btn-or">🪪 Badge</a>
                            <?php if ($s['test_complete']): ?>
                                <a href="<?= BASE_URL ?>/admin/download_diplome?id=<?= $s['id'] ?>" class="btn btn-sm btn-or">🎖️ Diplôme</a>
                                <a href="<?= BASE_URL ?>/admin/correction?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline">🔍 Correction</a>
                            <?php endif; ?>
                            <?php if (estSuperAdmin()): ?>
                                <a href="<?= BASE_URL ?>/admin/seminaristes?supprimer=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce séminariste ?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$seminaristes): ?><tr><td colspan="10">Aucun séminariste trouvé.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
