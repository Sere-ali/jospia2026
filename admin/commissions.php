<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

if (isset($_GET['supprimer']) && estSuperAdmin()) {
    $pdo->prepare("DELETE FROM comptes WHERE membre_id = ?")->execute([(int)$_GET['supprimer']]);
    $pdo->prepare("DELETE FROM membres_commission WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    redirect('/admin/commissions');
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
            <a href="<?= BASE_URL ?>/admin/badges_commission" class="btn btn-or btn-sm">🖨️ Imprimer tous les badges (4/page)</a>
        </div>

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
                    <tr><th>Photo</th><th>Matricule</th><th>Nom et prénoms</th><th>Commission</th><th>Contact</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($membres as $m): ?>
                    <tr>
                        <td><?php if ($m['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($m['photo']) ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;"><?php endif; ?></td>
                        <td class="mono"><?= e($m['matricule']) ?></td>
                        <td><?= e($m['nom_prenoms']) ?></td>
                        <td><span class="pill pill-vert"><?= e($m['commission']) ?></span></td>
                        <td><?= e($m['contact']) ?></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/edit_membre?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <a href="<?= BASE_URL ?>/admin/download_badge?id=<?= $m['id'] ?>" class="btn btn-sm btn-or">🪪 Badge</a>
                            <?php if (estSuperAdmin()): ?>
                                <a href="<?= BASE_URL ?>/admin/commissions?supprimer=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce membre ?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$membres): ?><tr><td colspan="6">Aucun membre trouvé.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
