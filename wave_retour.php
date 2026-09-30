<?php
/** Retour de Wave après le paiement : vérification auprès de Wave, puis validation automatique. */
require_once __DIR__ . '/includes/init.php';
exigerConnexion();
$compte = utilisateurCourant();
if ($compte['role'] !== 'seminariste') { redirect('/login'); }

$pid = (int)($_GET['p'] ?? 0);
$st = $pdo->prepare("SELECT * FROM paiements WHERE id = ? AND seminariste_id = ?");
$st->execute([$pid, (int)$compte['seminariste_id']]);
$p = $st->fetch();
if (!$p) { redirect('/paiement'); }
if ($p['statut'] === 'validé') { redirect('/espace/fiche'); }

if (waveApiActive() && $p['wave_session_id'] && waveVerifierEtValider($pdo, $p['wave_session_id'])) {
    redirect('/espace/fiche');
}
$titrePage = "Paiement non abouti";
require_once __DIR__ . '/includes/header.php';
?>
<div class="container" style="max-width:600px;margin-top:2rem;">
    <div class="card p-4">
        <div class="alert alert-erreur text-center">
            <strong>Le paiement n'a pas abouti.</strong><br>
            Aucun montant n'a été débité, ou votre solde Wave est insuffisant. Votre inscription n'est pas validée tant que le paiement n'est pas effectué.
        </div>
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/wave_pay" class="btn btn-wave">💙 Réessayer le paiement de <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
