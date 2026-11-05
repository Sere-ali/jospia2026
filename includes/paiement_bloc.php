<?php
/** Bloc de paiement en espèces (affiché après l'inscription et sur paiement.php). */
?>
<div class="pay-bloc">
    <h3>Paiement des frais de participation</h3>
    <div class="pay-montant"><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA</div>
    <ol class="pay-etapes">
        <li>Remettez <strong><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA en espèces</strong> à la <strong>commission Finance</strong>.</li>
        <li>La commission Finance enregistre votre paiement. Vos <strong>identifiants de connexion</strong> et votre <strong>reçu avec QR code</strong> apparaissent alors sur votre espace (page « Suivre mon paiement »).</li>
    </ol>
    <small style="display:block;text-align:center;color:var(--texte-doux);margin-top:6px;">Votre QR code reste rouge tant que le paiement n'est pas enregistré par la commission Finance.</small>
</div>
