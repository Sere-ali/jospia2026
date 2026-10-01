<?php
require_once __DIR__ . '/init.php';
$u = utilisateurCourant();
$page = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titrePage) ? e($titrePage) . ' - ' : '' ?><?= EVENT_NAME ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.jpg">
<meta name="theme-color" content="#0B8A4E">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= @filemtime(__DIR__ . "/../assets/css/style.css") ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/theme.css?v=<?= @filemtime(__DIR__ . "/../assets/css/theme.css") ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/form.css?v=<?= @filemtime(__DIR__ . "/../assets/css/form.css") ?>">
<script>document.documentElement.classList.add('js');</script>
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a href="<?= BASE_URL ?>/" class="brand">
            <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo JOSPIA">
            <div class="brand-text">
                <div class="titre">JOSPIA <?= substr(EVENT_NAME, -4) ?></div>
                <div class="sous-titre">Journées Spirituelles Islamiques d'Anyama</div>
            </div>
        </a>
        <button type="button" class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="menu-principal"><span></span></button>
        <div class="nav-links" id="menu-principal">
            <a href="<?= BASE_URL ?>/" class="<?= $page === 'index.php' ? 'actif' : '' ?>">Accueil</a>
            <a href="<?= BASE_URL ?>/inscription_commission" class="<?= $page === 'inscription_commission.php' ? 'actif' : '' ?>">Commission</a>
            <a href="<?= BASE_URL ?>/inscription_seminariste" class="<?= $page === 'inscription_seminariste.php' ? 'actif' : '' ?>">Séminariste</a>
            <a href="<?= BASE_URL ?>/visiteur" class="<?= $page === 'visiteur.php' ? 'actif' : '' ?>">Visiteur</a>
            <?php if ($u): ?>
                <a href="<?= BASE_URL ?>/espace/fiche">Mon espace</a>
                <?php if (estAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard">Tableau de bord</a>
                <?php elseif (estFinance()): ?>
                    <a href="<?= BASE_URL ?>/finance/paiements">Commission finance</a>
                <?php elseif (($u['role'] ?? '') === 'securite'): ?>
                    <a href="<?= BASE_URL ?>/securite/visiteurs">Commission sécurité</a>
                <?php elseif (estScientifique()): ?>
                    <a href="<?= BASE_URL ?>/admin/commission_scientifique">Commission scientifique</a>
                <?php endif; ?>
                <?php if (commissionPropre($pdo)): ?><a href="<?= BASE_URL ?>/espace/rapport">Rapports</a><?php endif; ?>
                <?php if (!estAdmin() || !empty($u['membre_id']) || !empty($u['seminariste_id'])): ?><a href="<?= BASE_URL ?>/espace/sortie">Sortie camp</a><?php endif; ?>
                <a href="<?= BASE_URL ?>/compte">Mon compte</a>
                <a href="<?= BASE_URL ?>/logout" class="btn-nav">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn-nav">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php if ($u && ($seCours = sortieEnCours($pdo, $u['id']))): $seRetard = strtotime($seCours['heure_retour']) < time(); ?>
<?php if (!$seRetard): ?>
<div class="alerte-sortie alerte-ok" id="info-sortie"><span>✅ <strong>Vous êtes autorisé à sortir.</strong> S'il vous plaît, rentrez avant <?= e(date('H:i', strtotime($seCours['heure_retour']))) ?> (<?= e(date('d/m/Y', strtotime($seCours['heure_retour']))) ?>).</span></div>
<?php endif; ?>
<div id="alerte-sortie" class="alerte-sortie" data-retour="<?= (int)strtotime($seCours['heure_retour']) ?>" data-now="<?= time() ?>" <?= $seRetard ? '' : 'hidden' ?>>
    <span>⏰ <strong>Votre heure de sortie est épuisée. Veuillez retourner sur le camp, s'il vous plaît.</strong></span>
    <form method="post" action="<?= BASE_URL ?>/espace/sortie"><input type="hidden" name="action" value="rentre"><input type="hidden" name="id" value="<?= (int)$seCours['id'] ?>"><button class="btn btn-sm">🏠 Je suis rentré</button></form>
</div>
<?php elseif ($u && ($seRefus = sortieRefusee($pdo, $u['id']))): ?>
<div class="alerte-sortie">
    <span>✖ <strong>Votre requête de sortie a été refusée.</strong><?= $seRefus['refus_motif'] ? ' Motif : ' . e($seRefus['refus_motif']) : '' ?></span>
    <form method="post" action="<?= BASE_URL ?>/espace/sortie"><input type="hidden" name="action" value="vu"><input type="hidden" name="id" value="<?= (int)$seRefus['id'] ?>"><button class="btn btn-sm">OK</button></form>
</div>
<?php endif; ?>
<div id="contenu-page">
