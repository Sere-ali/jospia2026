<?php
/** Attend en entrée : $membre (ligne de membres_commission) */
?>
<div class="badge-jos-com">
    <img class="badge-jos-com__entete" src="<?= BASE_URL ?>/assets/img/badge_entete_commission.png" alt="JOSPIA 2026 — AEEMCI">
    <div class="badge-jos-com__corps">
        <div class="badge-jos-com__photo-wrap">
            <?php if ($membre['photo']): ?>
                <img src="<?= BASE_URL ?>/uploads/photos/<?= e($membre['photo']) ?>" alt="Photo">
            <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Photo">
            <?php endif; ?>
        </div>
        <div class="badge-jos-com__nom"><?= e($membre['nom_prenoms']) ?></div>
        <div class="badge-jos-com__divider"><span class="ligne"></span><span class="losange"></span><span class="ligne"></span></div>
        <div class="badge-jos-com__commission">Commission<br><?= e($membre['commission']) ?></div>
        <div class="badge-jos-com__dates">
            <div class="badge-jos-com__date-box">
                <div class="jour"><?= e(EVENT_JOUR_DEBUT) ?></div>
                <div class="mois"><?= e(EVENT_MOIS_ANNEE) ?></div>
            </div>
            <span class="badge-jos-com__fleche">➤</span>
            <div class="badge-jos-com__date-box">
                <div class="jour"><?= e(EVENT_JOUR_FIN) ?></div>
                <div class="mois"><?= e(EVENT_MOIS_ANNEE) ?></div>
            </div>
        </div>
    </div>
</div>
