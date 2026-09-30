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

[$code, $j] = waveApi('POST', '/v1/checkout/sessions', [
    'amount' => (string)FRAIS_PARTICIPATION,
    'currency' => 'XOF',
    'client_reference' => 'JOSPIA-P' . $pid,
    'success_url' => urlSite() . '/wave_retour?p=' . $pid,
    'error_url' => urlSite() . '/wave_retour?p=' . $pid . '&echec=1',
]);
if ($code >= 200 && $code < 300 && !empty($j['wave_launch_url']) && !empty($j['id'])) {
    $pdo->prepare("UPDATE paiements SET wave_session_id = ? WHERE id = ?")->execute([$j['id'], $pid]);
    header('Location: ' . $j['wave_launch_url']);
    exit;
}
error_log('Wave checkout échoué (' . $code . ') : ' . json_encode($j));
$_SESSION['flash_paiement'] = "Impossible d'ouvrir Wave pour le moment. Réessayez dans un instant.";
redirect('/paiement');
