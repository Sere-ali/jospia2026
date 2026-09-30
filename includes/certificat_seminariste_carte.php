<?php
/**
 * Certificat de participation officiel JOSPIA 2026 (séminaristes), généré automatiquement depuis la maquette fournie.
 * Attend en entrée : $nomCertificat. Seul le nom est inséré ; tout le reste est la maquette d'origine.
 */
?>
<div class="jos-doc jos-cert jos-cert-sem">
    <div class="jos-doc__stage">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/certificat_seminariste.jpg" alt="Certificat de participation JOSPIA 2026">
        <div class="jos-cert-sem__nom" data-fit="0.98"><span><?= e(mb_strtoupper($nomCertificat, 'UTF-8')) ?></span></div>
    </div>
</div>
