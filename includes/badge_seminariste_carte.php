<?php
/**
 * Badge officiel « Séminariste » JOSPIA 2026, généré automatiquement depuis la maquette fournie.
 * Attend en entrée : $s (ligne de seminaristes). Seuls photo, nom, dortoir, niveau et matricule sont insérés.
 */
$__avatar = BASE_URL . '/assets/img/avatar.svg';
$__photo = !empty($s['photo']) ? BASE_URL . '/uploads/photos/' . rawurlencode($s['photo']) : $__avatar;
$__nom = mb_strtoupper((string)$s['nom_prenoms'], 'UTF-8');
?>
<div class="jos-doc jos-badge jos-badge-sem">
    <div class="jos-doc__stage">
        <img class="jos-badge-sem__photo" src="<?= e($__photo) ?>" alt="" onerror="this.onerror=null;this.src='<?= e($__avatar) ?>'">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/badge_seminariste.webp" alt="Badge JOSPIA 2026 - Séminariste">
        <div class="jos-badge-sem__nom" data-fit="0.98"><span><?= e($__nom) ?></span></div>
        <div class="jos-badge-sem__val v1" data-fit="0.97"><span><?= e($s['dortoir']) ?></span></div>
        <div class="jos-badge-sem__val v2" data-fit="0.97"><span><?= e($s['niveau_affecte'] ?: 'Non affecté') ?></span></div>
        <div class="jos-badge-sem__val v3" data-fit="0.97"><span><?= e($s['matricule']) ?></span></div>
    </div>
</div>
