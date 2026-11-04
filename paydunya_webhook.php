<?php
/** Notification PayDunya (IPN) : confirme le paiement côté serveur, même si la personne ferme la page. */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$token = $_POST['data']['invoice']['token'] ?? '';
if (paydunyaActive() && is_string($token) && preg_match('/^[A-Za-z0-9_\-]{5,80}$/', $token)) {
    // On ne fait jamais confiance au contenu reçu : on interroge PayDunya directement.
    waveVerifierEtValider($pdo, 'pd_' . $token);
}
http_response_code(200);
echo 'ok';
