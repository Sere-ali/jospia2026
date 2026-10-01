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
                <a href="<?= BASE_URL ?>/compte">Mon compte</a>
                <a href="<?= BASE_URL ?>/logout" class="btn-nav">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login" class="btn-nav">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div id="contenu-page">
