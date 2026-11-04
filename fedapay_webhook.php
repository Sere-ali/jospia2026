<?php
/** Notification FedaPay (webhook) : confirme le paiement côté serveur, même si la personne ferme la page. */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
$j = json_decode(file_get_contents('php://input'), true);
$id = $j['entity']['id'] ?? ($j['object']['id'] ?? null);
if (fedapayActive() && is_numeric($id)) {
    // On ne fait jamais confiance au contenu reçu : on interroge FedaPay directement.
    waveVerifierEtValider($pdo, 'fp_' . (int)$id);
}
http_response_code(200);
echo 'ok';
