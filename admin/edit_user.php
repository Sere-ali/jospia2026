<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM comptes WHERE id = ? AND role IN ('admin','superadmin','finance')");
$stmt->execute([$id]);
$compte = $stmt->fetch();
if (!$compte) { die("Compte introuvable."); }

$estPrincipal = ($compte['identifiant'] === 'superadmin');
$erreurs = [];
$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_affiche'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $role = $_POST['role'] ?? $compte['role'];
    $nouveauMdp = $_POST['nouveau_mot_de_passe'] ?? '';

    if ($nom === '' || $identifiant === '') {
        $erreurs[] = "Le nom et l'identifiant sont obligatoires.";
    } elseif (!in_array($role, ['admin', 'superadmin', 'finance'], true)) {
        $erreurs[] = "Rôle invalide.";
    } elseif ($nouveauMdp !== '' && strlen($nouveauMdp) < 6) {
        $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } else {
        if ($estPrincipal) {
            // Le compte superadmin principal garde son identifiant et son rôle (protection anti-verrouillage)
            $identifiant = $compte['identifiant'];
            $role = $compte['role'];
        } else {
            $chk = $pdo->prepare("SELECT COUNT(*) FROM comptes WHERE identifiant = ? AND id != ?");
            $chk->execute([$identifiant, $id]);
            if ($chk->fetchColumn() > 0) $erreurs[] = "Cet identifiant est déjà utilisé par un autre compte.";
        }

        if (empty($erreurs)) {
            if ($nouveauMdp !== '') {
                $hash = password_hash($nouveauMdp, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE comptes SET nom_affiche=?, identifiant=?, role=?, mot_de_passe=? WHERE id=?")
                    ->execute([$nom, $identifiant, $role, $hash, $id]);
            } else {
                $pdo->prepare("UPDATE comptes SET nom_affiche=?, identifiant=?, role=? WHERE id=?")
                    ->execute([$nom, $identifiant, $role, $id]);
            }
            $stmt = $pdo->prepare("SELECT * FROM comptes WHERE id = ?");
            $stmt->execute([$id]);
            $compte = $stmt->fetch();
            $succes = "Compte mis à jour.";
        }
    }
}

$titrePage = "Modifier — " . $compte['nom_affiche'];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline btn-sm">&larr; Retour à la liste</a>

        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <h2>Modifier — <?= e($compte['nom_affiche']) ?></h2>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <?php if ($estPrincipal): ?>
            <div class="alert alert-info">🔒 Ce compte est le Super Admin principal : son identifiant et son rôle sont protégés (non modifiables) pour éviter de vous verrouiller hors du site.</div>
        <?php endif; ?>

        <form method="post">
            <fieldset>
                <legend>Informations du compte</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom_affiche" required value="<?= e($compte['nom_affiche']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Identifiant de connexion</label>
                        <input type="text" name="identifiant" required value="<?= e($compte['identifiant']) ?>" <?= $estPrincipal ? 'disabled' : '' ?>>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" name="nouveau_mot_de_passe" minlength="6" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Rôle</label>
                        <select name="role" <?= $estPrincipal ? 'disabled' : '' ?>>
                            <option value="admin" <?= $compte['role']==='admin'?'selected':'' ?>>Administrateur</option>
                            <option value="superadmin" <?= $compte['role']==='superadmin'?'selected':'' ?>>Super Administrateur</option>
                            <option value="finance" <?= $compte['role']==='finance'?'selected':'' ?>>Commission Finance</option>
                        </select>
                    </div>
                </div>
            </fieldset>
            <button class="btn btn-primaire btn-block">Enregistrer les modifications</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
