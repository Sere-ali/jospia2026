<?php
/** Lien secret : la commission scientifique remplit les niveaux sans compte (le lien est géré par le super admin). */
require_once __DIR__ . '/includes/init.php';
$t = (string)($_GET['t'] ?? '');
try {
    $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'lien_niveaux'");
    $st->execute();
    $ok = $st->fetchColumn();
} catch (Throwable $e) { $ok = false; }
if (!$ok || $t === '' || !hash_equals((string)$ok, $t)) {
    http_response_code(403);
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Lien invalide</h2><p>Ce lien n\'est plus actif. Demandez un nouveau lien à l\'administration.</p></div>');
}
header('X-Robots-Tag: noindex, nofollow');
$PARTAGE = true;
require __DIR__ . '/admin/seuils_niveaux.php';
