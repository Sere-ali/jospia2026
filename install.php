<?php
/**
 * JOSPIA 2026 - Script d'installation (méthode alternative)
 * À utiliser UNIQUEMENT si vous n'avez pas importé sql/jospia2026_complet.sql.
 * 1) Créez d'abord une base vide nommée "jospia2026" dans phpMyAdmin/MySQL.
 * 2) Ouvrez ce fichier dans le navigateur : http://localhost/jospia2026/install.php
 * 3) Supprimez ou renommez ce fichier une fois l'installation terminée.
 */
require_once __DIR__ . '/config/db.php';

$messages = [];
$erreur = false;

try {
    $sql = file_get_contents(__DIR__ . '/sql/jospia.sql');
    $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
    $sql = preg_replace('/USE\s+jospia2026;/i', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        $sansCommentaires = preg_replace('/^\s*--.*$/m', '', $stmt);
        if (trim($sansCommentaires) === '') continue;
        $pdo->exec($stmt);
    }
    $messages[] = "Tables créées avec succès.";

    $count = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
    if ($count == 0) {
        require __DIR__ . '/sql/questions_seed.php';
        $ins = $pdo->prepare("INSERT INTO questions (categorie, enonce, option_a, option_b, option_c, option_d, bonne_reponse) VALUES (?,?,?,?,?,?,?)");
        foreach ($questionsSeed as $q) {
            $ins->execute($q);
        }
        $messages[] = count($questionsSeed) . " questions du test d'entrée ajoutées.";
    } else {
        $messages[] = "Questions déjà présentes, étape ignorée.";
    }

    $existe = $pdo->query("SELECT COUNT(*) FROM comptes WHERE role='superadmin'")->fetchColumn();
    if ($existe == 0) {
        $motDePasse = 'jospia2026';
        $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO comptes (identifiant, mot_de_passe, role, nom_affiche) VALUES (?,?,?,?)")
            ->execute(['superadmin', $hash, 'superadmin', 'Super Administrateur']);
        $messages[] = "Compte Super Admin créé → identifiant : <strong>superadmin</strong> / mot de passe : <strong>$motDePasse</strong> (à changer après la première connexion).";
    } else {
        $messages[] = "Un compte Super Admin existe déjà, étape ignorée.";
    }

} catch (Exception $e) {
    $erreur = true;
    $messages[] = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><title>Installation JOSPIA 2026</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:640px;padding-top:60px;">
    <div class="carte">
        <h2>Installation JOSPIA 2026</h2>
        <?php foreach ($messages as $m): ?>
            <div class="alert <?= $erreur ? 'alert-erreur' : 'alert-succes' ?>"><?= $m ?></div>
        <?php endforeach; ?>
        <?php if (!$erreur): ?>
            <p><strong>⚠️ Important :</strong> supprimez ou renommez le fichier <code>install.php</code> maintenant, puis rendez-vous sur la <a href="<?= BASE_URL ?>/login">page de connexion</a>.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
