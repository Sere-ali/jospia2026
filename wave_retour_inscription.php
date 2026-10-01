<?php
/** Retour de Wave pour une inscription : le dossier n'est créé que si Wave confirme le paiement. */
require_once __DIR__ . '/includes/init.php';
inscriptionsAttentePreparer($pdo);
$jeton = preg_replace('/[^a-f0-9]/', '', (string)($_GET['t'] ?? ''));
$st = $pdo->prepare("SELECT * FROM inscriptions_attente WHERE jeton = ?");
$st->execute([$jeton]);
$att = $st->fetch();
if (!$att) { redirect('/inscription_seminariste'); }

$sid = (int)($att['seminariste_id'] ?? 0);
if (!$sid && waveApiActive() && $att['wave_session_id'] && empty($_GET['echec'])) {
    waveVerifierEtValider($pdo, $att['wave_session_id']);
    $st->execute([$jeton]); $att = $st->fetch();
    $sid = (int)($att['seminariste_id'] ?? 0);
}
if ($sid) {
    // Paiement confirmé : on connecte la personne sur son nouvel espace
    $stC = $pdo->prepare("SELECT * FROM comptes WHERE seminariste_id = ? AND role = 'seminariste' LIMIT 1");
    $stC->execute([$sid]);
    if ($compte = $stC->fetch()) {
        session_regenerate_id(true);
        $_SESSION['compte_id'] = $compte['id'];
        $_SESSION['compte'] = $compte;
        redirect('/espace/fiche');
    }
    redirect('/login');
}
$titrePage = "Paiement non abouti";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" style="max-width:600px;margin:2rem auto;">
    <div class="carte">
        <div class="alert alert-erreur text-center">
            <strong>Échec : impossible de soumettre vos informations.</strong><br>
            Le paiement n'a pas été effectué. Tant que le paiement n'a pas abouti, votre inscription n'est pas enregistrée.
        </div>
        <p style="text-align:center;"><a href="<?= BASE_URL ?>/wave_inscription?t=<?= e($jeton) ?>" class="btn btn-primaire">💙 Réessayer le paiement de <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
