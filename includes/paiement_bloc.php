<?php
/**
 * Bloc de paiement Wave (affiché après l'inscription et sur paiement.php).
 * Variable optionnelle : $paiementAction (URL du formulaire, défaut /paiement.php)
 */
$paiementAction = $paiementAction ?? (BASE_URL . '/paiement.php');
$numeroPayeur = null;
if (!empty($_SESSION['compte']['seminariste_id'])) {
    $stNP = $pdo->prepare("SELECT numero_wave FROM paiements WHERE seminariste_id = ? ORDER BY id DESC LIMIT 1");
    $stNP->execute([$_SESSION['compte']['seminariste_id']]);
    $numeroPayeur = $stNP->fetchColumn() ?: null;
}
$lienWaveConfigure = lienWavePaiement() !== null;
?>
<div class="pay-bloc">
    <h3>💳 Paiement des frais de participation</h3>
    <div class="pay-montant"><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</div>
    <ol class="pay-etapes">
        <li>Ouvrez <strong>Wave</strong> et envoyez <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong><?php if ($numeroPayeur): ?> <strong>depuis votre numéro <?= e($numeroPayeur) ?></strong><?php endif; ?> au numéro :
            <div class="pay-numero">
                <span id="wave-numero"><?= e(numeroWaveAffiche()) ?></span>
                <button type="button" class="btn btn-sm btn-outline" onclick="navigator.clipboard && navigator.clipboard.writeText('<?= e(WAVE_NUMERO) ?>'); this.textContent='Copié ✓';">Copier</button>
            </div>
            <?php if ($lienWaveConfigure): ?>
                <a href="<?= e(lienWavePaiement()) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-wave">Payer <?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA avec Wave</a>
            <?php endif; ?>
        </li>
        <li>La <strong>commission Finance</strong> reçoit votre paiement sur son compte Wave et le valide. Vos <strong>identifiants de connexion</strong> et votre <strong>reçu avec QR code</strong> apparaissent alors (page « Suivre mon paiement »).</li>
        <li><em>Facultatif</em> : pour accélérer la validation, saisissez l'<strong>ID de transaction</strong> (SMS ou application Wave, ex. <span class="mono">TCN...</span>).</li>
    </ol>
    <form method="post" action="<?= e($paiementAction) ?>">
        <div class="form-group">
            <label style="font-weight:bold;">ID de la transaction Wave (facultatif)</label>
            <input type="text" name="reference_transaction" placeholder="Ex : TCN1234ABCD" maxlength="100">
        </div>
        <button type="submit" class="btn btn-primaire btn-block">Envoyer l'ID de transaction</button>
    </form>
</div>
