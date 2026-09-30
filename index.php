<?php
$titrePage = "Accueil";
require_once __DIR__ . '/includes/header.php';
?>
<header class="hero rayons-bg">
    <div class="container">
        <span class="badge-annee">✦ Édition <?= substr(EVENT_NAME,-4) ?></span>
        <h1>Bienvenue à la plateforme officielle des <?= EVENT_FULL ?></h1>
        <p class="lead">Inscrivez-vous en tant que membre de commission ou séminariste, composez le test d'entrée en ligne, et retrouvez toutes vos informations sur votre espace personnel : fiche, dortoir, badge.</p>
        <div class="hero-actions">
            <a href="<?= BASE_URL ?>/inscription_commission.php" class="btn btn-primaire">👥 Rejoindre une commission</a>
            <a href="<?= BASE_URL ?>/inscription_seminariste.php" class="btn btn-or">🎓 M'inscrire comme séminariste</a>
        </div>
    </div>
</header>

<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Comment ça marche</span>
            <h2>Trois étapes simples</h2>
        </div>
        <div class="grid grid-3">
            <div class="carte carte-choix">
                <div class="icone">1</div>
                <h3>Inscrivez-vous</h3>
                <p>Remplissez le formulaire correspondant à votre profil : commission ou séminariste.</p>
            </div>
            <div class="carte carte-choix or">
                <div class="icone">2</div>
                <h3>Identifiants automatiques</h3>
                <p>Un compte est créé automatiquement pour accéder à votre espace personnel sécurisé.</p>
            </div>
            <div class="carte carte-choix">
                <div class="icone">3</div>
                <h3>Fiche, dortoir & badge</h3>
                <p>Consultez et imprimez votre fiche d'inscription, votre dortoir attribué, votre badge ou diplôme.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background:var(--vert-teinte);">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Deux profils</span>
            <h2>Choisissez votre formulaire d'inscription</h2>
        </div>
        <div class="grid grid-2">
            <div class="carte">
                <h3>🗂️ Membre de commission</h3>
                <p style="color:var(--texte-doux)">Pour les encadreurs et responsables des commissions : MG, MGA, Administration, Scientifique, MIC, Finance, Santé, Sécurité, Hygiène, Pépinière, Restauration, Logistique, Protocole.</p>
                <p style="color:var(--texte-doux)">Un badge officiel est généré automatiquement (téléchargeable uniquement par les administrateurs).</p>
                <a href="<?= BASE_URL ?>/inscription_commission.php" class="btn btn-primaire btn-block">S'inscrire comme membre</a>
            </div>
            <div class="carte">
                <h3>🎓 Séminariste</h3>
                <p style="color:var(--texte-doux)">Après inscription, vous composerez un test d'entrée en ligne (/20) qui déterminera votre niveau, votre sous-comité et votre dortoir.</p>
                <p style="color:var(--texte-doux)">Une fiche d'inscription imprimable avec le nom du dortoir est générée automatiquement.</p>
                <a href="<?= BASE_URL ?>/inscription_seminariste.php" class="btn btn-or btn-block">S'inscrire comme séminariste</a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
