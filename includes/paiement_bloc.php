<?php
/**
 * Bloc de paiement Wave (affiché après l'inscription et sur paiement.php).
 * Variable optionnelle : $paiementAction (URL du formulaire, défaut /paiement.php)
 */
$paiementAction = $paiementAction ?? (BASE_URL . '/paiement.php');
$lienWaveConfigure = strpos(WAVE_PAYMENT_LINK, 'VOTRE_MARCHAND_ID') === false;
?>
<div class="pay-bloc">
    <h3>💳 Paiement des frais de participation</h3>
    <div class="pay-montant"><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</div>
    <ol class="pay-etapes">
        <li>Ouvrez <strong>Wave</strong> et envoyez <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</strong> au numéro :
            <div class="pay-numero">
                <span id="wave-numero"><?= e(numeroWaveAffiche()) ?></span>
                <button type="button" class="btn btn-sm btn-outline" onclick="navigator.clipboard && navigator.clipboard.writeText('<?= e(WAVE_NUMERO) ?>'); this.textContent='Copié ✓';">Copier</button>
            </div>
            <?php if ($lienWaveConfigure): ?>
                <a href="<?= e(WAVE_PAYMENT_LINK) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-wave">Payer avec Wave</a>
            <?php endif; ?>
        </li>
        <li>Relevez l'<strong>ID de transaction</strong> (dans le SMS de Wave ou dans l'application, ex. <span class="mono">TCN...</span>).</li>
        <li>Saisissez-le ci-dessous puis validez. La <strong>commission Finance</strong> vérifie votre paiement et votre <strong>reçu avec QR code</strong> apparaît sur votre espace.</li>
    </ol>
    <form method="post" action="<?= e($paiementAction) ?>">
        <div class="form-group">
            <label style="font-weight:bold;">ID / référence de la transaction Wave</label>
            <input type="text" name="reference_transaction" placeholder="Ex : TCN1234ABCD" required maxlength="100">
        </div>
        <button type="submit" class="btn btn-primaire btn-block">J'ai payé — Envoyer pour validation</button>
    </form>
</div>
