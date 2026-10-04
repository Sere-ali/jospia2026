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
            $erreurs[] = "Une visite est déjà en cours pour ce numéro (arrivée le " . dateHeureAffiche($ouverte) . "). Validez d'abord sa sortie dans « Je pars ».";
        }
        if (!$erreurs) {
            $pdo->prepare("INSERT INTO visiteurs (nom_prenoms, contact, motif, heure_arrivee) VALUES (?,?,?,?)")->execute([$nom, $contact, $motif, $arrivee]);
            $succes = "Arrivée enregistrée à " . dateHeureAffiche($arrivee) . ". À votre départ, trouvez votre nom dans « Je pars » et validez votre sortie.";
            $_POST = [];
        }
    }
    if ($mode === 'sortie') {
        $erreurs = [];
        $idV = (int)($_POST['id'] ?? 0);
        $sortie = dateHeureSaisie($_POST['heure_sortie'] ?? '') ?: date('Y-m-d H:i:s');
        $st = $pdo->prepare("SELECT id, nom_prenoms, heure_arrivee FROM visiteurs WHERE id = ? AND heure_sortie IS NULL");
        $st->execute([$idV]);
        $v = $st->fetch();
        if (!$v) {
            $erreurs[] = "Cette visite n'est plus en cours.";
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
$presents = $pdo->query("SELECT id, nom_prenoms, motif, heure_arrivee FROM visiteurs WHERE heure_sortie IS NULL ORDER BY heure_arrivee DESC LIMIT 200")->fetchAll();
?>
<section class="section form-page">
<div class="container"><?= programmeBandeau($pdo) ?></div>
    <div class="container" style="max-width:1000px;">
        <div class="section-titre form-titre" style="text-align:center;">
            <span class="eyebrow">Accueil des visiteurs</span>
            <h2>Visiteurs - arrivée et sortie</h2>
            <p class="form-intro">Enregistrez votre arrivée, puis validez votre sortie en un clic dans la liste. Suivi assuré par la commission Sécurité.</p>
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
                    <div class="help-text">À votre départ, vous validerez simplement votre sortie dans la liste « Je pars ».</div>
                </fieldset>
                <button type="submit" class="btn btn-primaire btn-block btn-envoi"><span>Enregistrer mon arrivée</span><i aria-hidden="true">→</i></button>
            </form>

            <form method="post" class="form-pro" novalidate data-ajax>
                <input type="hidden" name="mode" value="sortie">
                <fieldset>
                    <legend data-ico="🚪">Je pars</legend>
                    <div class="form-group"><label>Heure de sortie</label>
                        <input type="datetime-local" name="heure_sortie" value="<?= e($maintenant) ?>"></div>
                    <div data-live="presents">
                    <?php if ($presents): ?>
                        <div class="form-group"><label>Trouvez votre nom, puis validez votre sortie</label>
                            <input type="text" id="filtre-visiteurs" placeholder="Rechercher mon nom..." autocomplete="off"></div>
                        <div class="liste-sortie">
                            <?php foreach ($presents as $v): ?>
                                <div class="ligne-sortie" data-nom="<?= e(mb_strtolower($v['nom_prenoms'], 'UTF-8')) ?>">
                                    <div><strong><?= e($v['nom_prenoms']) ?></strong>
                                        <small>Arrivé à <?= e(dateHeureAffiche($v['heure_arrivee'])) ?><?= $v['motif'] !== '' ? ' · ' . e($v['motif']) : '' ?></small></div>
                                    <button type="submit" name="id" value="<?= (int)$v['id'] ?>" class="btn btn-sm btn-or">Valider ma sortie</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="help-text">Aucun visiteur n'est présent actuellement.</div>
                    <?php endif; ?>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</section>
<script>
(function () {
    function filtrer() {
        var champ = document.getElementById('filtre-visiteurs');
        var q = champ ? champ.value.trim().toLowerCase() : '';
        document.querySelectorAll('.ligne-sortie').forEach(function (l) {
            l.style.display = (!q || l.getAttribute('data-nom').indexOf(q) !== -1) ? '' : 'none';
        });
    }
    document.addEventListener('input', function (e) { if (e.target && e.target.id === 'filtre-visiteurs') filtrer(); });
    document.addEventListener('jos:maj', filtrer);
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
