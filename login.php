<?php
$titrePage = "Connexion";
require_once __DIR__ . '/includes/header.php';

if (estConnecte()) {
    redirect(estAdmin() ? '/admin/dashboard.php' : '/espace/fiche.php');
}

$erreur = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM comptes WHERE identifiant = ? AND actif = 1");
    $stmt->execute([$identifiant]);
    $compte = $stmt->fetch();

    if ($compte && password_verify($motDePasse, $compte['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['compte_id'] = $compte['id'];
        $_SESSION['compte'] = $compte;
        redirect(in_array($compte['role'], ['admin','superadmin'], true) ? '/admin/dashboard.php' : ($compte['role'] === 'finance' ? '/finance/paiements.php' : '/espace/fiche.php'));
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>
<div class="container">
    <div class="login-box">
        <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-login" alt="Logo JOSPIA">
        <h2>Connexion</h2>
        <?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Identifiant (contact ou nom d'utilisateur)</label>
                <input type="text" name="identifiant" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn btn-primaire btn-block">Se connecter</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--texte-doux);">
            Pas encore inscrit ? <a href="<?= BASE_URL ?>/index.php">Choisir un formulaire d'inscription</a>
        </p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
