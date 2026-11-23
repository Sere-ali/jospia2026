<?php
$titrePage = "Responsables de commission — Présentation";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/responsables_pres.php';
respPresPreparer($pdo);
$erreurs = []; $succes = null;
$anneeMax = (int)date('Y');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim((string)($_POST['site'] ?? '')) !== '') { http_response_code(400); exit; } // anti-robot
    $nom = trim($_POST['nom_prenoms'] ?? '');
    $commission = trim($_POST['commission'] ?? '');
    $profil = 'Responsable'; // présentation des responsables : profil automatique
    $annee = (int)($_POST['annee_debut'] ?? 0);
    $niveau = trim($_POST['niveau_fonction'] ?? '');
    if ($nom === '') $erreurs[] = "Le nom et prénom(s) sont obligatoires.";
    if (!in_array($commission, listeCommissions(), true)) $erreurs[] = "Veuillez choisir votre commission.";
    if ($annee < 1990 || $annee > $anneeMax) $erreurs[] = "L'année de début doit être comprise entre 1990 et $anneeMax.";
    if ($niveau === '') $erreurs[] = "Indiquez votre profession.";
    $existant = null;
    if (!$erreurs) {
        $st = $pdo->prepare("SELECT * FROM presentation_responsables WHERE commission = ? AND nom_prenoms = ?");
        $st->execute([$commission, $nom]); $existant = $st->fetch();
    }
    if (!$erreurs && empty($_FILES['photo']['name']) && !$existant) $erreurs[] = "La photo d'identité est obligatoire.";
    $photo = $existant['photo'] ?? '';
    if (!$erreurs && !empty($_FILES['photo']['name'])) {
        $p = uploadPhoto($_FILES['photo']);
        if (!$p) $erreurs[] = "La photo n'a pas pu être enregistrée (jpg, jpeg, png ou webp, 5 Mo max)."; else $photo = $p;
    }
    if (!$erreurs) {
        $pdo->prepare("INSERT INTO presentation_responsables (commission, nom_prenoms, profil, annee_debut, niveau_fonction, photo) VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE profil = VALUES(profil), annee_debut = VALUES(annee_debut), niveau_fonction = VALUES(niveau_fonction), photo = VALUES(photo)")
            ->execute([$commission, mb_substr($nom, 0, 190), mb_substr($profil, 0, 190), $annee, mb_substr($niveau, 0, 255), $photo]);
        $succes = $existant ? "Votre fiche a bien été mise à jour." : "Merci ! Votre fiche est enregistrée : vous serez présenté(e) lors de la présentation des responsables.";
    }
}
?>
<section class="section form-page">
    <div class="container form-wrap" style="max-width:680px;">
        <div class="section-titre form-titre">
            <span class="eyebrow">Responsables de commission</span>
            <h2>Inscription pour la présentation</h2>
            <p class="form-intro">Remplissez cette fiche pour être présenté(e) lors de la présentation des responsables. Les champs marqués <span class="req">*</span> sont obligatoires.</p>
        </div>
        <?php if ($succes): ?>
            <div class="alert alert-succes"><?= e($succes) ?></div>
            <a href="<?= BASE_URL ?>/inscription_responsable" class="btn btn-outline">Remplir une autre fiche</a>
        <?php else: ?>
            <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
            <form method="post" enctype="multipart/form-data" novalidate class="form-pro">
                <input type="text" name="site" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;height:0;width:0;opacity:0" aria-hidden="true">
                <fieldset>
                    <legend>Ma fiche de présentation</legend>
                    <div class="form-group"><label>1- Nom et prénom(s) <span class="req">*</span></label>
                        <input type="text" name="nom_prenoms" required value="<?= e($_POST['nom_prenoms'] ?? '') ?>"></div>
                    <div class="form-group"><label>Commission <span class="req">*</span></label>
                        <select name="commission" required><option value="">- Choisir ma commission -</option>
                        <?php foreach (listeCommissions() as $c): ?><option value="<?= e($c) ?>" <?= (($_POST['commission'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
                    <div class="form-group"><label>2- Année de début de militantisme ou de participation aux JOSPIA <span class="req">*</span></label>
                        <input type="number" name="annee_debut" inputmode="numeric" min="1990" max="<?= $anneeMax ?>" required placeholder="Ex : 2015" value="<?= e($_POST['annee_debut'] ?? '') ?>"></div>
                    <div class="form-group"><label>3- Photo d'identité <span class="req">*</span></label>
                        <input type="file" name="photo" accept="image/*">
                        <div class="help-text">Format jpg/png/webp, 5 Mo max. Photo nette, visage bien visible.</div></div>
                    <div class="form-group"><label>4- Profession <span class="req">*</span></label>
                        <input type="text" name="niveau_fonction" required placeholder="Ex : Master 2 en Droit / Enseignant" value="<?= e($_POST['niveau_fonction'] ?? '') ?>"></div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Envoyer ma fiche</span><i aria-hidden="true">→</i></button>
            </form>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
