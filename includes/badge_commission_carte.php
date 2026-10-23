<?php
/**
 * Badge « Commission » JOSPIA 2026 (modèle orange) - généré automatiquement depuis la maquette fournie.
 * Attend en entrée : $membre (ligne de membres_commission). Seuls photo, nom/prénoms et commission sont insérés.
 * Repère de mise en page : 638 x 1016 (voir .jos-badge dans style.css et pdfBadgeCommission dans pdf_docs.php).
 */
$__avatar = BASE_URL . '/assets/img/avatar.svg';
$__f = photoFocus($GLOBALS['pdo'], $membre['photo'] ?? '', 192 / 446);
$__photo = !empty($membre['photo']) ? BASE_URL . '/uploads/photos/' . rawurlencode($membre['photo']) : $__avatar;
$__mep = josBadgeCommissionMiseEnPage($membre['nom_prenoms'], $membre['commission']);
?>
<div class="jos-doc jos-badge">
    <div class="jos-doc__stage" style="--dy:<?= (int)$__mep['dy'] ?>;--prs:<?= (int)$__mep['prTaille'] ?>;--prp:<?= (int)$__mep['prPas'] ?>;--cos:<?= (int)$__mep['comTaille'] ?>;">
        <img class="jos-badge__photo" src="<?= e($__photo) ?>" alt="" style="object-position:<?= round($__f[0]*100) ?>% <?= round($__f[1]*100) ?>%;" onerror="this.onerror=null;this.src='<?= e($__avatar) ?>'">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/badge_commission.webp" alt="Badge JOSPIA 2026 - Commission">
        <div class="jos-badge__n1" data-fit="1"><span><?= e($__mep['nom']) ?></span></div>
        <?php if ($__mep['prenoms']): ?>
        <div class="jos-badge__n2" data-fit="1">
            <?php foreach ($__mep['prenoms'] as $__l): ?><span><?= e($__l) ?></span><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="jos-badge__band" data-fit="0.96">
            <div class="valeur"><?php foreach ($__mep['com'] as $__l): ?><span><?= e($__l) ?></span><?php endforeach; ?></div>
        </div>
    </div>
</div>
