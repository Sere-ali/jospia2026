<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);
sortiesPreparer($pdo);
$titrePage = "Attribuer les sorties du camp";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['compte_id'] ?? 0);
    $role = $_POST['role'] ?? '';
    if ($id && in_array($role, ['mg', 'securite'], true)) {
        if (($_POST['action'] ?? '') === 'donner') {
            if (!sortieRoleAttribue($pdo, $id, $role)) $pdo->prepare("INSERT INTO sorties_roles (compte_id, role) VALUES (?, ?)")->execute([$id, $role]);
            journaliser($pdo, 'Rôle sortie attribué', "compte #$id : $role");
            $_SESSION['flash_succes'] = "Rôle attribué.";
        } else {
            $pdo->prepare("DELETE FROM sorties_roles WHERE compte_id = ? AND role = ?")->execute([$id, $role]);
            journaliser($pdo, 'Rôle sortie retiré', "compte #$id : $role");
            $_SESSION['flash_succes'] = "Rôle retiré.";
        }
    }
    redirect('/admin/sorties_roles');
}
$q = trim($_GET['q'] ?? '');
$sql = "SELECT c.id, c.identifiant, c.nom_affiche, c.role, m.commission FROM comptes c LEFT JOIN membres_commission m ON m.id = c.membre_id WHERE c.role <> 'seminariste'";
$par = [];
if ($q !== '') { $sql .= " AND (c.identifiant LIKE ? OR c.nom_affiche LIKE ? OR m.nom_prenoms LIKE ?)"; $par = ["%$q%", "%$q%", "%$q%"]; }
$st = $pdo->prepare($sql . " ORDER BY c.nom_affiche, c.identifiant LIMIT 300");
$st->execute($par);
$comptes = $st->fetchAll();
$roles = [];
foreach ($pdo->query("SELECT compte_id, role FROM sorties_roles")->fetchAll() as $r) $roles[(int)$r['compte_id']][$r['role']] = true;
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Sorties du camp</span><h2>Attribuer le MG / MGA</h2>
            <p>Les comptes attribués peuvent accepter ou refuser les demandes de sortie. La commission Sécurité confirme ensuite la sortie (bouton OK).</p></div>
        <form method="get" class="form-inline" style="margin-bottom:14px;"><input type="text" name="q" value="<?= e($q) ?>" placeholder="Rechercher un nom ou identifiant"> <button class="btn btn-primaire btn-sm">Rechercher</button></form>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Nom</th><th>Identifiant</th><th>Rôle / commission</th><th>MG / MGA</th></tr></thead>
            <tbody>
            <?php foreach ($comptes as $c): $cid = (int)$c['id']; ?>
                <tr>
                    <td><?= e($c['nom_affiche'] ?: '-') ?></td>
                    <td><?= e($c['identifiant']) ?></td>
                    <td><?= e($c['role'] . ($c['commission'] ? ' - ' . $c['commission'] : '')) ?></td>
                    <?php foreach (['mg'] as $rl): $a = !empty($roles[$cid][$rl]); ?>
                    <td><form method="post" style="margin:0;">
                        <input type="hidden" name="compte_id" value="<?= $cid ?>"><input type="hidden" name="role" value="<?= $rl ?>">
                        <?php if ($a): ?><span class="pill pill-vert">✔ Attribué</span> <button name="action" value="retirer" class="btn btn-outline btn-sm">Retirer</button>
                        <?php else: ?><button name="action" value="donner" class="btn btn-primaire btn-sm">Attribuer</button><?php endif; ?>
                    </form></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; if (!$comptes): ?><tr><td colspan="4">Aucun compte.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
        <p style="margin-top:12px;font-size:.9em;">Les administrateurs ont déjà accès aux deux pages sans attribution.</p>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
