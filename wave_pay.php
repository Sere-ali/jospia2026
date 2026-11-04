<?php
/** Démarre le paiement Wave automatique (API Checkout) puis envoie le séminariste sur Wave. */
require_once __DIR__ . '/includes/init.php';
exigerConnexion();
$compte = utilisateurCourant();
if ($compte['role'] !== 'seminariste') { redirect('/login'); }
if (!waveApiActive()) { redirect('/paiement'); }

$sid = (int)$compte['seminariste_id'];
$st = $pdo->prepare("SELECT * FROM paiements WHERE seminariste_id = ? ORDER BY id DESC LIMIT 1");
$st->execute([$sid]);
$p = $st->fetch();
if ($p && $p['statut'] === 'validé') { redirect('/espace/fiche'); }
if (!$p || $p['statut'] === 'rejeté') {
    $pdo->prepare("INSERT INTO paiements (seminariste_id, reference_transaction, statut, numero_wave, montant) VALUES (?, '', 'en attente', ?, ?)")
        ->execute([$sid, $p['numero_wave'] ?? null, FRAIS_PARTICIPATION]);
    $pid = (int)$pdo->lastInsertId();
} else {
    $pid = (int)$p['id'];
}

if (fedapayActive()) { redirect('/paiement_direct?p=' . $pid); }
$session = paiementCreerSession('JOSPIA-P' . $pid, urlSite() . '/wave_retour?p=' . $pid);
if ($session) {
    $pdo->prepare("UPDATE paiements SET wave_session_id = ? WHERE id = ?")->execute([$session[0], $pid]);
    header('Location: ' . $session[1]);
    exit;
}
$_SESSION['flash_paiement'] = "Impossible d'ouvrir la page de paiement pour le moment. Réessayez dans un instant.";
redirect('/paiement');
