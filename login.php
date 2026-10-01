<?php
$titrePage = "Connexion";
require_once __DIR__ . '/includes/header.php';

if (estConnecte()) {
    redirect(estAdmin() ? '/admin/dashboard' : (($_SESSION['compte']['role'] ?? '') === 'mg' ? '/admin/comite_manageriale' : '/espace/fiche'));
}

$erreur = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM comptes WHERE identifiant = ? AND actif = 1");
    $stmt->execute([$identifiant]);
    $compte = $stmt->fetch();
    if (!$compte && preg_match('/^[+0-9 .\-]+$/', $identifiant)) {
        // numéro saisi avec espaces ou indicatif 225 : on le ramène au format local
        $stmt->execute([numeroLocal($identifiant)]);
        $compte = $stmt->fetch();
    }

    if ($compte && password_verify($motDePasse, $compte['mot_de_passe'])) {
        session_regenerate_id(true);
        $_SESSION['compte_id'] = $compte['id'];
        $_SESSION['compte'] = $compte;
        if (in_array($compte['role'], ['admin', 'superadmin', 'finance', 'scientifique', 'securite'], true)) { journaliser($pdo, 'Connexion', '', $compte); }
        redirect(in_array($compte['role'], ['admin','superadmin'], true) ? '/admin/dashboard' : ($compte['role'] === 'finance' ? '/finance/paiements' : ($compte['role'] === 'scientifique' ? '/admin/commission_scientifique' : ($compte['role'] === 'mg' ? '/admin/comite_manageriale' : ($compte['role'] === 'securite' ? '/securite/visiteurs' : '/espace/fiche')))));
    } else {
        $erreur = "Identifiant ou mot de passe incorrect.";
    }
}
?>
<div class="form-page login-page"><div class="container">
    <div class="login-box">
        <img src="<?= BASE_URL ?>/assets/img/logo.jpg" class="logo-login" alt="Logo JOSPIA">
        <h2>Connexion</h2>
        <?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
        <form method="post" class="form-pro">
            <div class="form-group">
                <label>Identifiant (votre numéro sans 225, ou nom d'utilisateur)</label>
                <input type="text" name="identifiant" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="mot_de_passe" required>
            </div>
            <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Se connecter</span><i aria-hidden="true">→</i></button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--texte-doux);">
            Inscrit mais sans identifiants ? <a href="<?= BASE_URL ?>/statut">Suivre mon paiement</a><br>Pas encore inscrit ? <a href="<?= BASE_URL ?>/">Choisir un formulaire d'inscription</a>
        </p>
    </div>
</div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
