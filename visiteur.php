<?php
$titrePage = "Visiteurs";
require_once __DIR__ . '/includes/header.php';
date_default_timezone_set('Africa/Abidjan');
visiteursPreparer($pdo);

$erreurs = []; $succes = null; $mode = $_POST['mode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = numeroLocal($_POST['contact'] ?? '');
    if ($mode === 'arrivee' && ($contact === '' || !preg_match('/^[0-9]{10}$/', $contact))) {
        $erreurs[] = "Le contact doit contenir uniquement des chiffres (10 chiffres).";
    }
    if ($mode === 'arrivee' && !$erreurs) {
        $nom = trim($_POST['nom_prenoms'] ?? '');
        $motif = mb_substr(trim($_POST['motif'] ?? ''), 0, 255);
        $arrivee = dateHeureSaisie($_POST['heure_arrivee'] ?? '') ?: date('Y-m-d H:i:s');
        if ($nom === '') $erreurs[] = "Le nom et prénoms sont obligatoires.";
        if ($motif === '') $erreurs[] = "Le motif de la visite est obligatoire.";
        $st = $pdo->prepare("SELECT heure_arrivee FROM visiteurs WHERE contact = ? AND heure_sortie IS NULL ORDER BY heure_arrivee DESC LIMIT 1");
        $st->execute([$contact]);
        if ($ouverte = $st->fetchColumn()) {
            $erreurs[] = "Une visite est déjà en cours pour ce numéro (arrivée le " . dateHeureAffiche($ouverte) . "). La commission Sécurité doit d'abord enregistrer sa sortie.";
        }
        if (!$erreurs) {
            $pdo->prepare("INSERT INTO visiteurs (nom_prenoms, contact, motif, heure_arrivee) VALUES (?,?,?,?)")->execute([$nom, $contact, $motif, $arrivee]);
            $succes = "Arrivée enregistrée à " . dateHeureAffiche($arrivee) . ". À votre départ, présentez-vous à la commission Sécurité : elle validera votre sortie.";
            $_POST = [];
        }
    }
}
$maintenant = date('Y-m-d\TH:i');
?>
<section class="section form-page">
<div class="container"><?= programmeBandeau($pdo) ?></div>
    <div class="container" style="max-width:1000px;">
        <div class="section-titre form-titre" style="text-align:center;">
            <span class="eyebrow">Accueil des visiteurs</span>
            <h2>Visiteurs - arrivée et sortie</h2>
            <p class="form-intro">Enregistrez votre arrivée. À votre départ, la commission Sécurité valide votre sortie.</p>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="grid grid-2" style="align-items:start;">
            <form method="post" class="form-pro" novalidate data-ajax>
                <input type="hidden" name="mode" value="arrivee">
                <fieldset>
                    <legend data-ico="🚶">Je suis arrivé</legend>
                    <div class="form-group"><label>Nom et prénoms <span class="req">*</span></label>
                        <input type="text" name="nom_prenoms" required value="<?= e($mode === 'arrivee' ? ($_POST['nom_prenoms'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Contact (téléphone) <span class="req">*</span></label>
                        <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required placeholder="Ex : 0700000000" value="<?= e($mode === 'arrivee' ? ($_POST['contact'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Motif de la visite <span class="req">*</span></label>
                        <input type="text" name="motif" required maxlength="255" placeholder="Ex : Visite à un séminariste, livraison, rendez-vous..." value="<?= e($mode === 'arrivee' ? ($_POST['motif'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Heure d'arrivée</label>
                        <input type="datetime-local" name="heure_arrivee" value="<?= e($maintenant) ?>"></div>
                    <div class="help-text">La liste des visiteurs et la validation des sorties sont réservées à la commission Sécurité.</div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Enregistrer mon arrivée</span><i aria-hidden="true">→</i></button>
            </form>

            <div class="carte">
                <h3>🚪 Départ</h3>
                <p>La sortie d'un visiteur est enregistrée uniquement par la commission Sécurité. Avant de quitter le site, passez la voir.</p>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
