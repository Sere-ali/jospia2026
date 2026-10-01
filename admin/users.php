<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);
rolesPreparer($pdo);

$erreurs = [];
$succes = null;

if (isset($_GET['desactiver'])) {
    $pdo->prepare("UPDATE comptes SET actif = 1 - actif WHERE id = ? AND role IN ('admin','finance','scientifique','securite')")->execute([(int)$_GET['desactiver']]);
    redirect('/admin/users');
}
$messageSuppression = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_id'])) {
    $idCible = (int)$_POST['supprimer_id'];
    $stmt = $pdo->prepare("SELECT id, identifiant, nom_affiche, role FROM comptes WHERE id = ? AND role IN ('admin','superadmin','finance','scientifique','securite')");
    $stmt->execute([$idCible]);
    $cible = $stmt->fetch();
    if (!$cible) {
        $erreurs[] = "Compte introuvable.";
    } elseif ($cible['role'] === 'superadmin') {
        $erreurs[] = "Un compte Super Administrateur ne peut pas être supprimé.";
    } elseif ($idCible === (int)$_SESSION['compte_id']) {
        $erreurs[] = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        try {
            $pdo->prepare("UPDATE paiements SET admin_validateur_id = NULL WHERE admin_validateur_id = ?")->execute([$idCible]);
            $pdo->prepare("DELETE FROM comptes WHERE id = ?")->execute([$idCible]);
            $succes = "Le compte de " . $cible['nom_affiche'] . " a été supprimé.";
        } catch (Throwable $e) {
            error_log('Suppression compte échouée : ' . $e->getMessage());
            $erreurs[] = "Suppression impossible (compte lié à des données). Désactivez-le à la place.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['supprimer_id'])) {
    $nom = trim($_POST['nom_affiche'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if ($nom === '' || $identifiant === '' || strlen($mdp) < 6) {
        $erreurs[] = "Nom, identifiant obligatoires. Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!in_array($role, ['admin','superadmin','finance','scientifique','securite'], true)) {
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

$comptes = $pdo->query("SELECT * FROM comptes WHERE role IN ('admin','superadmin','finance','scientifique','securite') ORDER BY role, nom_affiche")->fetchAll();

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
                            <option value="finance">Commission finance (valide les paiements, scanner)</option>
                            <option value="scientifique">Commission scientifique (notes, bulletins, quiz)</option>
                            <option value="securite">Commission sécurité (visiteurs)</option>
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
                        <td><?php
                            $libelles = ['superadmin' => '👑 Super Administrateur', 'admin' => 'Administrateur', 'finance' => '💰 Commission finance', 'scientifique' => '🔬 Commission scientifique', 'securite' => '🛡️ Commission sécurité'];
                            $lib = $libelles[$c['role']] ?? $c['role'];
                        ?><span class="pill <?= $c['role'] === 'superadmin' ? 'pill-or' : 'pill-vert' ?>" <?= $c['role'] === 'superadmin' ? 'style="background:#C89A3E;color:#fff;font-weight:800;"' : '' ?>><?= e($lib) ?></span></td>
                        <td><?= $c['actif'] ? '<span class="pill pill-vert">Actif</span>' : '<span class="pill pill-rouge">Désactivé</span>' ?></td>
                        <td style="white-space:nowrap;">
                            <?php if ($c['role'] !== 'superadmin' || (int)$c['id'] === (int)$_SESSION['compte_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/edit_user?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline">✏️ Modifier</a>
                            <?php endif; ?>
                            <?php if ($c['role'] !== 'superadmin'): ?>
                                <a href="<?= BASE_URL ?>/admin/users?desactiver=<?= $c['id'] ?>" class="btn btn-sm btn-outline"><?= $c['actif'] ? 'Désactiver' : 'Activer' ?></a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer définitivement le compte de <?= e(addslashes($c['nom_affiche'])) ?> ?');">
                                    <input type="hidden" name="supprimer_id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">🗑️ Supprimer</button>
                                </form>
                            <?php else: ?>
                                <span class="help-text"><?= (int)$c['id'] === (int)$_SESSION['compte_id'] ? 'Votre compte' : 'Réservé à son propriétaire' ?></span>
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
