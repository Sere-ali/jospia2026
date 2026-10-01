<?php
$titrePage = "Visiteurs";
require_once __DIR__ . '/includes/header.php';
date_default_timezone_set('Africa/Abidjan');
visiteursPreparer($pdo);

$erreurs = []; $succes = null; $mode = $_POST['mode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact = numeroLocal($_POST['contact'] ?? '');
    if ($contact === '' || !preg_match('/^[0-9]{8,15}$/', $contact)) {
        $erreurs[] = "Le contact doit contenir uniquement des chiffres (8 à 15).";
    }
    if ($mode === 'arrivee' && !$erreurs) {
        $nom = trim($_POST['nom_prenoms'] ?? '');
        $arrivee = dateHeureSaisie($_POST['heure_arrivee'] ?? '') ?: date('Y-m-d H:i:s');
        if ($nom === '') $erreurs[] = "Le nom et prénoms sont obligatoires.";
        $st = $pdo->prepare("SELECT heure_arrivee FROM visiteurs WHERE contact = ? AND heure_sortie IS NULL ORDER BY heure_arrivee DESC LIMIT 1");
        $st->execute([$contact]);
        if ($ouverte = $st->fetchColumn()) {
            $erreurs[] = "Une visite est déjà en cours pour ce numéro (arrivée le " . dateHeureAffiche($ouverte) . "). Déclarez d'abord votre sortie.";
        }
        if (!$erreurs) {
            $pdo->prepare("INSERT INTO visiteurs (nom_prenoms, contact, heure_arrivee) VALUES (?,?,?)")->execute([$nom, $contact, $arrivee]);
            $succes = "Arrivée enregistrée à " . dateHeureAffiche($arrivee) . ". Votre identifiant de sortie est votre numéro : $contact. À votre départ, utilisez « Déclarer ma sortie » ci-dessous.";
            $_POST = [];
        }
    } elseif ($mode === 'sortie' && !$erreurs) {
        $sortie = dateHeureSaisie($_POST['heure_sortie'] ?? '') ?: date('Y-m-d H:i:s');
        $st = $pdo->prepare("SELECT id, nom_prenoms, heure_arrivee FROM visiteurs WHERE contact = ? AND heure_sortie IS NULL ORDER BY heure_arrivee DESC LIMIT 1");
        $st->execute([$contact]);
        $v = $st->fetch();
        if (!$v) {
            $erreurs[] = "Aucune visite en cours pour ce numéro. Vérifiez le numéro utilisé à l'arrivée.";
        } elseif (strtotime($sortie) < strtotime($v['heure_arrivee'])) {
            $erreurs[] = "L'heure de sortie ne peut pas être avant l'heure d'arrivée (" . dateHeureAffiche($v['heure_arrivee']) . ").";
        } else {
            $pdo->prepare("UPDATE visiteurs SET heure_sortie = ? WHERE id = ?")->execute([$sortie, $v['id']]);
            $succes = "Sortie enregistrée pour " . $v['nom_prenoms'] . " à " . dateHeureAffiche($sortie) . ". Merci de votre visite !";
            $_POST = [];
        }
    }
}
$maintenant = date('Y-m-d\TH:i');
?>
<section class="section form-page">
    <div class="container" style="max-width:1000px;">
        <div class="section-titre form-titre" style="text-align:center;">
            <span class="eyebrow">Accueil des visiteurs</span>
            <h2>Visiteurs - arrivée et sortie</h2>
            <p class="form-intro">Enregistrez votre arrivée, puis déclarez votre sortie avec votre numéro de téléphone. Suivi assuré par la commission Sécurité.</p>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="grid grid-2" style="align-items:start;">
            <form method="post" class="form-pro" novalidate>
                <input type="hidden" name="mode" value="arrivee">
                <fieldset>
                    <legend data-ico="🚶">Je suis arrivé</legend>
                    <div class="form-group"><label>Nom et prénoms <span class="req">*</span></label>
                        <input type="text" name="nom_prenoms" required value="<?= e($mode === 'arrivee' ? ($_POST['nom_prenoms'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Contact (téléphone) <span class="req">*</span></label>
                        <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" required placeholder="Ex : 0700000000" value="<?= e($mode === 'arrivee' ? ($_POST['contact'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Heure d'arrivée</label>
                        <input type="datetime-local" name="heure_arrivee" value="<?= e($maintenant) ?>"></div>
                    <div class="help-text">Votre numéro de téléphone sert d'identifiant pour déclarer votre sortie.</div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Enregistrer mon arrivée</span><i aria-hidden="true">→</i></button>
            </form>

            <form method="post" class="form-pro" novalidate>
                <input type="hidden" name="mode" value="sortie">
                <fieldset>
                    <legend data-ico="🚪">Je pars</legend>
                    <div class="form-group"><label>Mon identifiant (numéro de téléphone) <span class="req">*</span></label>
                        <input type="tel" name="contact" inputmode="numeric" pattern="[0-9]{8,15}" maxlength="15" required placeholder="Le numéro donné à l'arrivée" value="<?= e($mode === 'sortie' ? ($_POST['contact'] ?? '') : '') ?>"></div>
                    <div class="form-group"><label>Heure de sortie</label>
                        <input type="datetime-local" name="heure_sortie" value="<?= e($maintenant) ?>"></div>
                    <div class="help-text">L'heure de sortie est enregistrée sur votre visite en cours.</div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Déclarer ma sortie</span><i aria-hidden="true">→</i></button>
            </form>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
