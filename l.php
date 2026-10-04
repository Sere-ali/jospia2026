<?php
/** Lien court /l/CODE : redirige vers un formulaire ou ouvre la page « niveaux » (commission scientifique). */
require_once __DIR__ . '/includes/init.php';
$code = (string)($_GET['c'] ?? '');
$cible = null;
if (preg_match('/^[a-z0-9]{4,20}$/', $code)) {
    try {
        liensCourtsPreparer($pdo);
        $st = $pdo->prepare("SELECT cible FROM liens_courts WHERE code = ?");
        $st->execute([$code]);
        $cible = $st->fetchColumn() ?: null;
    } catch (Throwable $e) { $cible = null; }
}
if (!$cible || !isset(LIENS_COURTS[$cible])) {
    http_response_code(404);
    die('<div style="font-family:sans-serif;padding:40px;text-align:center;color:#8a1f1f;"><h2>Lien invalide</h2><p>Ce lien n\'est plus actif. Demandez un nouveau lien à l\'administration.</p></div>');
}
if ($cible === 'niveaux') {
    header('X-Robots-Tag: noindex, nofollow');
    $PARTAGE = true;
    require __DIR__ . '/admin/seuils_niveaux.php';
    exit;
}
redirect(LIENS_COURTS[$cible][1]);
