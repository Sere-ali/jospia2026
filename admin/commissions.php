<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

if (isset($_GET['supprimer']) && estSuperAdmin()) {
    $pdo->prepare("DELETE FROM comptes WHERE membre_id = ?")->execute([(int)$_GET['supprimer']]);
    $pdo->prepare("DELETE FROM membres_commission WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    redirect('/admin/commissions');
}

responsablePreparer($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['maj_responsable'])) {
    $pdo->prepare("UPDATE membres_commission SET responsable = ? WHERE id = ?")->execute([isset($_POST['responsable']) ? 1 : 0, (int)$_POST['id']]);
    journaliser($pdo, 'Responsable de commission', 'membre ' . (int)$_POST['id'] . ' : ' . (isset($_POST['responsable']) ? 'oui' : 'non'));
}

$recherche = trim($_GET['q'] ?? '');
$filtreCommission = $_GET['commission'] ?? '';

$sql = "SELECT * FROM membres_commission WHERE 1=1";
$params = [];
if ($recherche !== '') {
    $sql .= " AND (nom_prenoms LIKE ? OR matricule LIKE ? OR contact LIKE ?)";
    $params[] = "%$recherche%"; $params[] = "%$recherche%"; $params[] = "%$recherche%";
}
if ($filtreCommission !== '') {
    $sql .= " AND commission = ?";
    $params[] = $filtreCommission;
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$membres = $stmt->fetchAll();

$titrePage = "Membres de commission";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h2>Membres de commission (<?= count($membres) ?>)</h2>
            <a href="<?= BASE_URL ?>/admin/badges_commission" class="btn btn-or btn-sm">⬇️ Télécharger tous les badges en PDF (4/page)</a>
        </div>

        <?php require __DIR__ . '/../includes/graphiques_commissions.php'; ?>

        <form method="get" class="carte" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
            <div class="form-group" style="flex:1;min-width:200px;margin:0;">
                <label>Rechercher</label>
                <input type="text" name="q" value="<?= e($recherche) ?>" placeholder="Nom, matricule, contact...">
            </div>
            <div class="form-group" style="min-width:200px;margin:0;">
                <label>Commission</label>
                <select name="commission">
                    <option value="">Toutes</option>
                    <?php foreach (listeCommissions() as $c): ?>
                        <option value="<?= e($c) ?>" <?= $filtreCommission === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primaire">Filtrer</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Photo</th><th>Matricule</th><th>Nom et prénoms</th><th>Commission</th><th>Contact</th><th>Responsable de commission</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($membres as $m): ?>
                    <tr>
                        <td><?php if ($m['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($m['photo']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                        <td class="mono"><?= e($m['matricule']) ?></td>
                        <td><?= e($m['nom_prenoms']) ?></td>
                        <td><span class="pill pill-vert"><?= e($m['commission']) ?></span></td>
                        <td><?= e($m['contact']) ?></td>
                        <td style="text-align:center;"><form method="post" data-ajax><input type="hidden" name="maj_responsable" value="1"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><input type="checkbox" name="responsable" value="1" style="width:22px;height:22px;" <?= !empty($m['responsable']) ? 'checked' : '' ?> onchange="this.form.requestSubmit()" aria-label="Responsable de commission"></form></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/edit_membre?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <a href="<?= BASE_URL ?>/admin/download_badge?id=<?= $m['id'] ?>" class="btn btn-sm btn-or">🪪 Badge</a>
                            <a href="<?= BASE_URL ?>/admin/identifiants?q=<?= urlencode($m['matricule']) ?>" class="btn btn-sm btn-outline">🔑 Identifiant</a>
                            <a href="<?= BASE_URL ?>/admin/download_diplome_membre?id=<?= $m['id'] ?>" class="btn btn-sm btn-primaire">🎓 Diplôme</a>
                            <?php if (estSuperAdmin()): ?>
                                <a href="<?= BASE_URL ?>/admin/commissions?supprimer=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce membre ?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$membres): ?><tr><td colspan="7">Aucun membre trouvé.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
