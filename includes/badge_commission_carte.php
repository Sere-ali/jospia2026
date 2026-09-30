<?php
/**
 * Badge officiel « Commission » JOSPIA 2026 - généré automatiquement depuis la maquette fournie.
 * Attend en entrée : $membre (ligne de membres_commission). Seuls photo, nom et commission sont insérés.
 */
$__photo = (!empty($membre['photo']) && is_file(__DIR__ . '/../uploads/photos/' . $membre['photo']))
    ? BASE_URL . '/uploads/photos/' . rawurlencode($membre['photo'])
    : BASE_URL . '/assets/img/avatar.svg';
$__lignes = josLignesNom($membre['nom_prenoms']);
$__commission = mb_strtoupper((string)$membre['commission'], 'UTF-8');
?>
<div class="jos-doc jos-badge">
    <div class="jos-doc__stage">
        <img class="jos-badge__photo" src="<?= e($__photo) ?>" alt="">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/badge_commission.webp" alt="Badge JOSPIA 2026 - Commission">
        <div class="jos-badge__nom" data-fit="0.96">
            <?php foreach ($__lignes as $__l): ?><span><?= e($__l) ?></span><?php endforeach; ?>
        </div>
        <div class="jos-badge__com" data-fit="0.62">
            <div class="deco g"><i></i><b></b></div>
            <div class="valeur"><?= e($__commission) ?></div>
            <div class="deco d"><i></i><b></b></div>
        </div>
    </div>
</div>
