<?php
/** Paiement AVANT inscription : ouvre Wave pour une inscription en attente (rien n'est enregistré tant que le paiement n'a pas abouti). */
require_once __DIR__ . '/includes/init.php';
inscriptionsAttentePreparer($pdo);
$jeton = preg_replace('/[^a-f0-9]/', '', (string)($_GET['t'] ?? ''));
$st = $pdo->prepare("SELECT * FROM inscriptions_attente WHERE jeton = ?");
$st->execute([$jeton]);
$att = $st->fetch();
if (!$att || !waveApiActive()) { redirect('/inscription_seminariste'); }
if (!empty($att['seminariste_id'])) { redirect('/login'); }

[$code, $j] = waveApi('POST', '/v1/checkout/sessions', [
    'amount' => (string)FRAIS_PARTICIPATION,
    'currency' => 'XOF',
    'client_reference' => 'JOSPIA-I' . (int)$att['id'],
    'success_url' => urlSite() . '/wave_retour_inscription?t=' . $jeton,
    'error_url' => urlSite() . '/wave_retour_inscription?t=' . $jeton . '&echec=1',
]);
if ($code >= 200 && $code < 300 && !empty($j['wave_launch_url']) && !empty($j['id'])) {
    $pdo->prepare("UPDATE inscriptions_attente SET wave_session_id = ? WHERE id = ?")->execute([$j['id'], $att['id']]);
    header('Location: ' . $j['wave_launch_url']);
    exit;
}
error_log('Wave checkout (inscription) échoué (' . $code . ') : ' . json_encode($j));
$titrePage = "Paiement impossible";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" style="max-width:600px;margin:2rem auto;">
    <div class="carte">
        <div class="alert alert-erreur text-center"><strong>Échec : impossible de soumettre vos informations.</strong><br>Wave n'a pas pu être ouvert pour le moment, le paiement n'a donc pas été effectué. Aucune information n'a été enregistrée.</div>
        <p style="text-align:center;"><a href="<?= BASE_URL ?>/wave_inscription?t=<?= e($jeton) ?>" class="btn btn-primaire">💙 Réessayer le paiement</a></p>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
