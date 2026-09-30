<?php
require_once __DIR__ . '/../includes/init.php';
exigerConnexion();
$u = utilisateurCourant();
$modifierMoiMeme = true;
if ($u['role'] === 'membre') {
    require __DIR__ . '/../admin/edit_membre.php';
} elseif ($u['role'] === 'seminariste') {
    require __DIR__ . '/../admin/edit_seminariste.php';
} else {
    redirect('/espace/fiche');
}
