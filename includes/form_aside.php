<?php
/** Panneau latéral des formulaires d'inscription. Variable : $asideType ('seminariste' | 'commission') */
$asideType = $asideType ?? 'seminariste';
$estSem = ($asideType === 'seminariste');
?>
<aside class="form-aside">
    <div class="fa-card">
        <div class="fa-arche" aria-hidden="true"><span><?= $estSem ? '🎓' : '🤝' ?></span></div>
        <span class="fa-tag">JOSPIA 2026</span>
        <h3><?= $estSem ? 'Rejoignez les séminaristes' : 'Rejoignez l\'équipe d\'organisation' ?></h3>
        <p class="fa-sous"><?= $estSem ? 'Quelques minutes suffisent pour réserver votre place.' : 'Mettez vos talents au service de la Journée.' ?></p>

        <div class="fa-progress" aria-live="polite">
            <div class="fa-progress__top"><span>Formulaire complété</span><b data-progress-txt>0%</b></div>
            <div class="fa-progress__bar"><i data-progress-bar></i></div>
        </div>

        <ol class="fa-etapes">
            <li class="en-cours"><span>1</span><div><b>Remplir le formulaire</b><small>Vos informations en toute simplicité</small></div></li>
            <?php if ($estSem): ?>
            <li><span>2</span><div><b>Payer par Wave</b><small><?= number_format(FRAIS_PARTICIPATION, 0, ',', ' ') ?> FCFA de frais de participation</small></div></li>
            <li><span>3</span><div><b>Recevoir votre reçu</b><small>Identifiants + reçu avec QR code</small></div></li>
            <?php else: ?>
            <li><span>2</span><div><b>Recevoir vos identifiants</b><small>Affichés dès la validation</small></div></li>
            <li><span>3</span><div><b>Obtenir votre badge</b><small>Généré automatiquement</small></div></li>
            <?php endif; ?>
        </ol>
        <div class="fa-secu">🔒 Vos données restent confidentielles.</div>
    </div>
</aside>
