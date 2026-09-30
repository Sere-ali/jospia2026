<?php
/** Notification Wave (webhook) : confirme le paiement côté serveur, même si la personne ferme la page. */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$brut = file_get_contents('php://input');
$j = json_decode($brut, true);
$sessionId = $j['data']['id'] ?? ($j['id'] ?? '');
if (waveApiActive() && is_string($sessionId) && preg_match('/^[A-Za-z0-9_\-]{5,80}$/', $sessionId)) {
    // On ne fait jamais confiance au contenu reçu : on interroge Wave directement.
    waveVerifierEtValider($pdo, $sessionId);
}
http_response_code(200);
echo 'ok';
