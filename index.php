<?php
$titrePage = "Accueil";
require_once __DIR__ . '/includes/header.php';
$dateEvenement = (substr(EVENT_NAME, -4)) . '-12-' . str_pad(EVENT_JOUR_DEBUT, 2, '0', STR_PAD_LEFT) . 'T08:00:00';
?>
<?php require __DIR__ . '/includes/bandeau_aeemci.php'; ?>
<header class="hero">
    <div class="hero-motif" aria-hidden="true"></div>
    <div class="container hero-grid">
        <div class="hero-texte">
            <span class="badge-annee">Édition <?= e(substr(EVENT_NAME, -4)) ?> : inscriptions ouvertes</span>
            <h1>Bienvenue aux Journées Spirituelles Islamiques d'Anyama</h1>
            <p class="lead">Inscrivez-vous comme séminariste ou membre de commission, composez le test d'entrée en ligne, puis retrouvez votre fiche, votre dortoir et votre badge sur votre espace personnel.</p>
            <div class="hero-actions">
                <a href="<?= BASE_URL ?>/inscription_seminariste" class="btn btn-or">🎓 M'inscrire comme séminariste</a>
                <a href="<?= BASE_URL ?>/inscription_commission" class="btn btn-outline">👥 Rejoindre une commission</a>
                <a href="<?= BASE_URL ?>/visiteur" class="btn btn-outline">🚶 Espace visiteurs</a>
            </div>
            <div class="compte-rebours" data-cible="<?= e($dateEvenement) ?>" aria-live="off">
                <p class="compte-legende">Ouverture dans</p>
                <div class="cr"><b data-u="j">--</b><small>jours</small></div>
                <div class="cr"><b data-u="h">--</b><small>heures</small></div>
                <div class="cr"><b data-u="m">--</b><small>minutes</small></div>
                <div class="cr"><b data-u="s">--</b><small>secondes</small></div>
            </div>
        </div>
        <div class="hero-arche" aria-hidden="true">
            <div class="arche-cadre">
                <div class="arche-interieur">
                    <img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt="">
                    <div class="arche-dates">
                        <strong><?= e(EVENT_JOUR_DEBUT) ?> - <?= e(EVENT_JOUR_FIN) ?></strong>
                        <span><?= e(mb_convert_case(EVENT_MOIS_ANNEE, MB_CASE_TITLE, 'UTF-8')) ?></span>
                        <small>Collège privé Henriette Dagri-Diabaté d'Anyama</small>
                    </div>
                </div>
            </div>
            <i class="etoile e1"></i><i class="etoile e2"></i><i class="etoile e3"></i>
        </div>
    </div>
</header>
<div class="separateur-tricolore"></div>

<section class="section">
    <div class="container">
        <div class="section-titre rv">
            <span class="eyebrow">Comment ça marche</span>
            <h2>Trois étapes pour être prêt</h2>
        </div>
        <div class="grid grid-3">
            <div class="carte carte-choix rv" style="--d:0s">
                <div class="icone">1</div>
                <h3>Inscrivez-vous</h3>
                <p>Remplissez le formulaire de votre profil : séminariste ou membre de commission.</p>
            </div>
            <div class="carte carte-choix or rv" style="--d:.12s">
                <div class="icone">2</div>
                <h3>Recevez vos identifiants</h3>
                <p>Un compte est créé pour vous. Vos identifiants apparaissent dès que le paiement est validé.</p>
            </div>
            <div class="carte carte-choix rv" style="--d:.24s">
                <div class="icone">3</div>
                <h3>Fiche, dortoir et badge</h3>
                <p>Consultez votre fiche, votre dortoir, votre badge et votre diplôme sur votre espace.</p>
            </div>
        </div>
    </div>
</section>

<section class="section bande-profils">
    <div class="container">
        <div class="section-titre rv">
            <span class="eyebrow">Deux profils</span>
            <h2>Choisissez votre formulaire</h2>
        </div>
        <div class="grid grid-2">
            <div class="carte carte-profil orange rv">
                <div class="tete"><h3>🎓 Séminariste</h3></div>
                <div class="corps">
                    <p>Après l'inscription et le paiement des frais de participation, vous composez un test d'entrée en ligne noté sur 20. Il détermine votre niveau et votre dortoir.</p>
                    <p>Votre reçu de paiement avec QR code et votre fiche d'inscription sont générés automatiquement.</p>
                    <a href="<?= BASE_URL ?>/inscription_seminariste" class="btn btn-or btn-block">S'inscrire comme séminariste</a>
                </div>
            </div>
            <div class="carte carte-profil vert rv" style="--d:.12s">
                <div class="tete"><h3>👥 Membre de commission</h3></div>
                <div class="corps">
                    <p>Pour les encadreurs et responsables : MG, MGA, Administration, Scientifique, MIC, Finance, Santé, Sécurité, Hygiène, Pépinière, Restauration, Logistique, Protocole.</p>
                    <p>Votre badge officiel et votre certificat sont générés automatiquement.</p>
                    <a href="<?= BASE_URL ?>/inscription_commission" class="btn btn-primaire btn-block">S'inscrire comme membre</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
