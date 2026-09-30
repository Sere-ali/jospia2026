<?php
/** Attend en entrée : $s (ligne de seminaristes) */
?>
<div class="badge-jos">
    <img class="badge-jos__entete" src="<?= BASE_URL ?>/assets/img/badge_entete.png" alt="JOSPIA 2026 - AEEMCI">
    <div class="badge-jos__corps">
        <div class="badge-jos__photo-wrap">
            <?php if ($s['photo']): ?>
                <img src="<?= BASE_URL ?>/uploads/photos/<?= e($s['photo']) ?>" alt="Photo">
            <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Photo">
            <?php endif; ?>
        </div>
        <div class="badge-jos__nom"><?= e($s['nom_prenoms']) ?></div>
        <div class="badge-jos__divider"><span class="ligne"></span><span class="losange"></span><span class="ligne"></span></div>
        <div class="badge-jos__infos">
            <div class="ligne-info"><span class="label-info">Dortoir :</span><span class="valeur-info"><?= e($s['dortoir']) ?></span></div>
            <div class="ligne-info"><span class="label-info">Niveau :</span><span class="valeur-info"><?= e($s['niveau_affecte'] ?: 'Non affecté') ?></span></div>
            <div class="ligne-info"><span class="label-info">Matricule :</span><span class="valeur-info"><?= e($s['matricule']) ?></span></div>
        </div>
        <div class="badge-jos__dates">
            <div class="badge-jos__date-box">
                <div class="jour"><?= e(EVENT_JOUR_DEBUT) ?></div>
                <div class="mois"><?= e(EVENT_MOIS_ANNEE) ?></div>
            </div>
            <span class="badge-jos__fleche">➤</span>
            <div class="badge-jos__date-box">
                <div class="jour"><?= e(EVENT_JOUR_FIN) ?></div>
                <div class="mois"><?= e(EVENT_MOIS_ANNEE) ?></div>
            </div>
        </div>
    </div>
</div>
