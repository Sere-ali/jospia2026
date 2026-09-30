<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);

$erreurs = [];
$succes = null;

if (isset($_GET['desactiver'])) {
    $pdo->prepare("UPDATE comptes SET actif = 1 - actif WHERE id = ? AND role IN ('admin','superadmin','finance')")->execute([(int)$_GET['desactiver']]);
    redirect('/admin/users');
}
if (isset($_GET['supprimer'])) {
    $stmt = $pdo->prepare("SELECT identifiant FROM comptes WHERE id = ?");
    $stmt->execute([(int)$_GET['supprimer']]);
    $cible = $stmt->fetch();
    if ($cible && $cible['identifiant'] !== 'superadmin') {
        $pdo->prepare("DELETE FROM comptes WHERE id = ? AND role IN ('admin','superadmin','finance')")->execute([(int)$_GET['supprimer']]);
    }
    redirect('/admin/users');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_affiche'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if ($nom === '' || $identifiant === '' || strlen($mdp) < 6) {
        $erreurs[] = "Nom, identifiant obligatoires. Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!in_array($role, ['admin','superadmin','finance'], true)) {
        $erreurs[] = "Rôle invalide.";
    } else {
        $chk = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ?");
        $chk->execute([$identifiant]);
        if ($chk->fetchColumn() > 0) {
            $erreurs[] = "Cet identifiant est déjà utilisé.";
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO comptes (identifiant, mot_de_passe, role, nom_affiche) VALUES (?,?,?,?)")
                ->execute([$identifiant, $hash, $role, $nom]);
            $succes = "Compte $role créé pour $nom.";
        }
    }
}

$comptes = $pdo->query("SELECT * FROM comptes WHERE role IN ('admin','superadmin','finance') ORDER BY role, nom_affiche")->fetchAll();

$titrePage = "Comptes administrateurs";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;">
            <h2>Comptes Admin / Super Admin</h2>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="carte" style="margin-bottom:24px;">
            <h3>Créer un compte</h3>
            <form method="post">
                <div class="form-row">
                    <div class="form-group"><label>Nom complet</label><input type="text" name="nom_affiche" required></div>
                    <div class="form-group"><label>Identifiant de connexion</label><input type="text" name="identifiant" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Mot de passe</label><input type="password" name="mot_de_passe" required minlength="6"></div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role">
                            <option value="admin">Administrateur</option>
                            <option value="superadmin">Super Administrateur</option>
                            <option value="finance">Commission Finance (valide les paiements)</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primaire">Créer le compte</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Nom</th><th>Identifiant</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($comptes as $c): ?>
                    <tr>
                        <td><?= e($c['nom_affiche']) ?></td>
                        <td class="mono"><?= e($c['identifiant']) ?></td>
                        <td><span class="pill pill-vert"><?= e($c['role']) ?></span></td>
                        <td><?= $c['actif'] ? '<span class="pill pill-vert">Actif</span>' : '<span class="pill pill-rouge">Désactivé</span>' ?></td>
                        <td style="white-space:nowrap;">
                            <a href="<?= BASE_URL ?>/admin/edit_user?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <?php if ($c['identifiant'] !== 'superadmin'): ?>
                                <a href="<?= BASE_URL ?>/admin/users?desactiver=<?= $c['id'] ?>" class="btn btn-sm btn-outline"><?= $c['actif'] ? 'Désactiver' : 'Activer' ?></a>
                                <a href="<?= BASE_URL ?>/admin/users?supprimer=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce compte ?')">🗑️</a>
                            <?php else: ?>
                                <span class="help-text">Compte principal</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
