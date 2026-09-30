<?php
/**
 * Certificat de participation officiel JOSPIA 2026, généré automatiquement depuis la maquette fournie.
 * Entrées : $nomCertificat (nom et prénoms), $qualiteCertificat (ex : SEMINARISTE, MEMBRE DE LA COMMISSION MIC).
 * Seuls le nom et la qualité sont insérés ; tout le reste est la maquette d'origine.
 */
$qualiteCertificat = $qualiteCertificat ?? 'SEMINARISTE';
?>
<div class="jos-doc jos-cert">
    <div class="jos-doc__stage">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/certificat.jpg" alt="Certificat de participation JOSPIA 2026">
        <div class="jos-cert__nom" data-fit="0.98"><span><?= e(mb_strtoupper($nomCertificat, 'UTF-8')) ?></span></div>
        <div class="jos-cert__qualite" data-fit="0.96"><span>(<b>JOSPIA</b>) en qualité de <b><?= e(mb_strtoupper($qualiteCertificat, 'UTF-8')) ?></b> , tenue</span></div>
    </div>
</div>
