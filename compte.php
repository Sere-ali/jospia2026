<?php
require_once __DIR__ . '/includes/init.php';
exigerConnexion();
$u = utilisateurCourant();

$erreurs = [];
$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actuel = $_POST['mot_de_passe_actuel'] ?? '';
    $nouveau = $_POST['nouveau_mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM comptes WHERE id = ?");
    $stmt->execute([$u['id']]);
    $compte = $stmt->fetch();

    if (!$compte || !password_verify($actuel, $compte['mot_de_passe'])) {
        $erreurs[] = "Mot de passe actuel incorrect.";
    } elseif (strlen($nouveau) < 6) {
        $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } elseif ($nouveau !== $confirmation) {
        $erreurs[] = "La confirmation ne correspond pas au nouveau mot de passe.";
    } else {
        $hash = password_hash($nouveau, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE comptes SET mot_de_passe = ? WHERE id = ?")->execute([$hash, $u['id']]);
        $_SESSION['compte']['mot_de_passe'] = $hash;
        $succes = "Mot de passe modifié avec succès.";
    }
}

$titrePage = "Mon compte";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="login-box">
        <h2>Changer mon mot de passe</h2>
        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>Mot de passe actuel</label><input type="password" name="mot_de_passe_actuel" required></div>
            <div class="form-group"><label>Nouveau mot de passe</label><input type="password" name="nouveau_mot_de_passe" required minlength="6"></div>
            <div class="form-group"><label>Confirmer le nouveau mot de passe</label><input type="password" name="confirmation" required minlength="6"></div>
            <button class="btn btn-primaire btn-block">Enregistrer</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
