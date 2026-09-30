<?php
/**
 * Certificat de participation officiel JOSPIA 2026 — généré automatiquement depuis la maquette fournie.
 * Attend en entrée : $nomCertificat (nom et prénoms du séminariste). Seul le nom est inséré.
 */
?>
<div class="jos-doc jos-cert">
    <div class="jos-doc__stage">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/certificat.jpg" alt="Certificat de participation JOSPIA 2026">
        <div class="jos-cert__nom" data-fit="0.98"><span><?= e(mb_strtoupper($nomCertificat, 'UTF-8')) ?></span></div>
    </div>
</div>
