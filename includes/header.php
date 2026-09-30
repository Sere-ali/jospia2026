<?php
require_once __DIR__ . '/init.php';
$u = utilisateurCourant();
$page = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titrePage) ? e($titrePage) . ' — ' : '' ?><?= EVENT_NAME ?></title>
<link rel="icon" href="<?= BASE_URL ?>/assets/img/logo.jpg">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="container">
        <a href="<?= BASE_URL ?>/index.php" class="brand">
            <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo JOSPIA">
            <div class="brand-text">
                <div class="titre">JOSPIA <?= substr(EVENT_NAME, -4) ?></div>
                <div class="sous-titre">Journées Spirituelles Islamiques d'Anyama</div>
            </div>
        </a>
        <div class="nav-links">
            <a href="<?= BASE_URL ?>/index.php" class="<?= $page === 'index.php' ? 'actif' : '' ?>">Accueil</a>
            <a href="<?= BASE_URL ?>/inscription_commission.php" class="<?= $page === 'inscription_commission.php' ? 'actif' : '' ?>">Commission</a>
            <a href="<?= BASE_URL ?>/inscription_seminariste.php" class="<?= $page === 'inscription_seminariste.php' ? 'actif' : '' ?>">Séminariste</a>
            <?php if ($u): ?>
                <a href="<?= BASE_URL ?>/espace/fiche.php">Mon espace</a>
                <?php if (estAdmin()): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php">Tableau de bord</a>
                <?php elseif (estFinance()): ?>
                    <a href="<?= BASE_URL ?>/finance/paiements.php">Finance</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/compte.php">Mon compte</a>
                <a href="<?= BASE_URL ?>/logout.php" class="btn-nav">Déconnexion</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn-nav">Connexion</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
