<?php
/**
 * Badge « Séminariste » JOSPIA 2026 (modèle orange), généré depuis la maquette fournie.
 * Attend en entrée : $s (ligne de seminaristes). Seuls photo, nom/prénoms, matricule, dortoir et niveau sont insérés.
 * Repère de mise en page : 638 x 1016 (voir .jos-badge-sem dans style.css et pdfBadgeSeminariste dans pdf_docs.php).
 */
$__avatar = BASE_URL . '/assets/img/avatar.svg';
$__photo = !empty($s['photo']) ? BASE_URL . '/uploads/photos/' . rawurlencode($s['photo']) : $__avatar;
$__f = photoFocus($GLOBALS['pdo'], $s['photo'] ?? '', 192 / 265);
$__t = josBadgeSeminaristeTextes($s);
?>
<div class="jos-doc jos-badge jos-badge-sem">
    <div class="jos-doc__stage">
        <img class="jos-badge-sem__photo" src="<?= e($__photo) ?>" alt="" style="object-position:<?= round($__f[0]*100) ?>% <?= round($__f[1]*100) ?>%;" onerror="this.onerror=null;this.src='<?= e($__avatar) ?>'">
        <img class="jos-doc__modele" src="<?= BASE_URL ?>/assets/img/modeles/badge_seminariste.webp?v=<?= @filemtime(__DIR__ . '/../assets/img/modeles/badge_seminariste.webp') ?>" alt="Badge JOSPIA 2026 - Séminariste">
        <div class="jos-badge-sem__t t-nom" data-fit="1"><span><?= e($__t['nom']) ?></span></div>
        <?php if ($__t['prenoms'] !== ''): ?><div class="jos-badge-sem__t t-pre" data-fit="1"><span><?= e($__t['prenoms']) ?></span></div><?php endif; ?>
        <div class="jos-badge-sem__t t-mat" data-fit="1"><span><?= e($__t['matricule']) ?></span></div>
        <div class="jos-badge-sem__t t-dor" data-fit="1"><span><?= e($__t['dortoir']) ?></span></div>
        <div class="jos-badge-sem__t t-niv" data-fit="1"><span><?= e($__t['niveau']) ?></span></div>
    </div>
</div>
