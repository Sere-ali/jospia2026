<?php /** Bandeau « La JOSPIA est une activité de l'AEEMCI » : logo AEEMCI à gauche, logo JOSPIA à droite. */ ?>
<section class="lien-aeemci" aria-label="La JOSPIA est une activité de l'AEEMCI">
    <div class="la-fond" aria-hidden="true"></div>
    <div class="container la-grille">
        <div class="la-bloc la-gauche">
            <div class="la-medaillon"><img src="<?= BASE_URL ?>/assets/img/logo_aeemci.jpg" alt="Logo AEEMCI"></div>
            <div class="la-nom"><strong>AEEMCI</strong><small>Association des Élèves et Étudiants Musulmans de Côte d'Ivoire</small></div>
        </div>
        <div class="la-centre">
            <span class="la-trait"></span>
            <p>La <strong>JOSPIA</strong> est une activité de l'<strong>AEEMCI</strong></p>
            <span class="la-trait"></span>
        </div>
        <div class="la-bloc la-droite">
            <div class="la-nom"><strong>JOSPIA <?= e(substr(EVENT_NAME, -4)) ?></strong><small>Journées Spirituelles Islamiques d'Anyama</small></div>
            <div class="la-medaillon"><img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="Logo JOSPIA"></div>
        </div>
    </div>
</section>
