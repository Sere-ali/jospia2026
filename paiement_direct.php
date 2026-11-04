<?php
/** Paiement des frais directement sur le site : le séminariste choisit son opérateur et son numéro, puis valide sur son téléphone. */
require_once __DIR__ . '/includes/init.php';
if (!paiementDirectDisponible()) { redirect('/inscription_seminariste'); }

$jeton = preg_replace('/[^a-f0-9]/', '', (string)($_GET['t'] ?? ''));
$pid = (int)($_GET['p'] ?? 0);
$nom = ''; $contact = ''; $table = ''; $idLigne = 0; $session = ''; $retour = ''; $reference = '';

if ($jeton !== '') {
    inscriptionsAttentePreparer($pdo);
    $st = $pdo->prepare("SELECT * FROM inscriptions_attente WHERE jeton = ?");
    $st->execute([$jeton]);
    $ligne = $st->fetch();
    if (!$ligne) { redirect('/inscription_seminariste'); }
    if (!empty($ligne['seminariste_id'])) { redirect('/login'); }
    $d = json_decode($ligne['donnees'], true) ?: [];
    $nom = (string)($d['nom'] ?? ''); $contact = (string)($d['contact'] ?? '');
    $table = 'inscriptions_attente'; $idLigne = (int)$ligne['id']; $session = (string)($ligne['wave_session_id'] ?? '');
    $retour = urlSite() . '/wave_retour_inscription?t=' . $jeton;
    $reference = 'JOSPIA-I' . $idLigne;
    $lien = BASE_URL . '/paiement_direct?t=' . $jeton;
} else {
    exigerConnexion();
    $compte = utilisateurCourant();
    if ($compte['role'] !== 'seminariste') { redirect('/login'); }
    $st = $pdo->prepare("SELECT * FROM paiements WHERE id = ? AND seminariste_id = ?");
    $st->execute([$pid, (int)$compte['seminariste_id']]);
    $ligne = $st->fetch();
    if (!$ligne) { redirect('/paiement'); }
    if ($ligne['statut'] === 'validé') { redirect('/espace/fiche'); }
    $stS = $pdo->prepare("SELECT nom_prenoms, contact FROM seminaristes WHERE id = ?");
    $stS->execute([(int)$compte['seminariste_id']]);
    $s = $stS->fetch() ?: [];
    $nom = (string)($s['nom_prenoms'] ?? ''); $contact = (string)($s['contact'] ?? '');
    $table = 'paiements'; $idLigne = $pid; $session = (string)($ligne['wave_session_id'] ?? '');
    $retour = urlSite() . '/wave_retour?p=' . $pid;
    $reference = 'JOSPIA-P' . $pid;
    $lien = BASE_URL . '/paiement_direct?p=' . $pid;
}
$urlSuite = $jeton !== '' ? BASE_URL . '/wave_retour_inscription?t=' . $jeton : BASE_URL . '/wave_retour?p=' . $pid;

// Interrogation régulière depuis la page d'attente
if (isset($_GET['statut'])) {
    header('Content-Type: application/json');
    $etat = 'attente';
    if ($session !== '') {
        [$ok, , , $statut] = paiementInterroger($session);
        if ($ok) { $etat = 'ok'; } elseif (in_array($statut, ['declined', 'canceled', 'cancelled', 'failed'], true)) { $etat = 'echec'; }
    }
    echo json_encode(['etat' => $etat]);
    exit;
}

$erreur = null; $attente = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = paiementPayerDirect($reference, $nom, (string)($_POST['telephone'] ?? ''), (string)($_POST['operateur'] ?? ''), $retour, (string)($_POST['otp'] ?? ''));
    if (isset($r['erreur'])) {
        $erreur = $r['erreur'];
    } else {
        $pdo->prepare("UPDATE $table SET wave_session_id = ? WHERE id = ?")->execute([$r['id'], $idLigne]);
        if (!empty($r['url'])) { header('Location: ' . $r['url']); exit; }
        $session = $r['id']; $attente = true;
    }
}
$operateurs = ['mtn' => ['MTN', '#ffcc00', '#000'], 'orange' => ['Orange', '#ff7900', '#fff'], 'moov' => ['Moov', '#0072bc', '#fff'], 'wave' => ['Wave', '#1dc8ff', '#fff']];
$titrePage = "Paiement";
require_once __DIR__ . '/includes/header.php';
$montant = number_format(FRAIS_PARTICIPATION, 0, ',', ' ');
?>
<style>
.op-grille{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;}
.op-carte{position:relative;cursor:pointer;display:block;}
.op-carte input{position:absolute;opacity:0;pointer-events:none;}
.op-visuel{display:flex;align-items:center;justify-content:center;height:72px;border-radius:12px;border:3px solid transparent;box-shadow:0 0 0 1px #ccc;background:#fff;overflow:hidden;transition:.15s;}
.op-visuel img{max-width:80%;max-height:80%;object-fit:contain;}
.op-visuel b{font-size:1.15rem;}
.op-carte input:checked + .op-visuel{border-color:#0b8a4b;box-shadow:0 0 0 2px #0b8a4b;transform:scale(1.03);}
</style>
<div class="container" style="max-width:560px;margin:2rem auto;">
    <div class="carte">
        <h2 style="text-align:center;margin-top:0;">Frais de participation</h2>
        <div class="pay-montant" style="text-align:center;"><?= $montant ?> FCFA</div>
<?php if ($attente): ?>
        <div class="alert alert-info text-center" id="msg-attente"><strong>Validez le paiement sur votre téléphone.</strong><br>Une demande vient d'être envoyée à votre numéro. Confirmez avec votre code secret. Cette page se met à jour toute seule.</div>
        <div class="alert alert-erreur text-center" id="msg-echec" style="display:none;"><strong>Le paiement n'a pas abouti.</strong><br>Aucun montant n'a été débité. <a href="<?= e($lien) ?>">Réessayer</a></div>
        <script>
        (function () {
            var fin = false;
            function verifier() {
                if (fin) return;
                fetch('<?= e($lien) ?><?= strpos($lien, '?') === false ? '?' : '&' ?>statut=1', {credentials: 'same-origin'})
                    .then(function (r) { return r.json(); })
                    .then(function (j) {
                        if (j.etat === 'ok') { fin = true; window.location.href = '<?= e($urlSuite) ?>'; }
                        else if (j.etat === 'echec') { fin = true; document.getElementById('msg-attente').style.display = 'none'; document.getElementById('msg-echec').style.display = 'block'; }
                    }).catch(function () {});
            }
            setInterval(verifier, 3000);
        })();
        </script>
<?php else: ?>
<?php if ($erreur): ?><div class="alert alert-erreur"><?= e($erreur) ?></div><?php endif; ?>
        <form method="post" data-no-ajax>
            <div style="font-weight:600;margin-bottom:8px;">Choisissez votre moyen de paiement</div>
            <div class="op-grille">
<?php foreach ($operateurs as $cle => [$libelle, $couleur, $texte]): $logo = null; foreach (['svg', 'png', 'webp', 'jpg'] as $ext) { if (is_file(__DIR__ . '/assets/img/operateurs/' . $cle . '.' . $ext)) { $logo = BASE_URL . '/assets/img/operateurs/' . $cle . '.' . $ext . '?v=' . filemtime(__DIR__ . '/assets/img/operateurs/' . $cle . '.' . $ext); break; } } ?>
                <label class="op-carte">
                    <input type="radio" name="operateur" value="<?= e($cle) ?>"<?= $cle === 'mtn' ? ' checked' : '' ?>>
                    <span class="op-visuel" style="<?= $logo ? '' : 'background:' . e($couleur) . ';color:' . e($texte) . ';' ?>">
                        <?php if ($logo): ?><img src="<?= e($logo) ?>" alt="<?= e($libelle) ?>"><?php else: ?><b><?= e($libelle) ?></b><?php endif; ?>
                    </span>
                </label>
<?php endforeach; ?>
            </div>
            <label for="telephone" style="font-weight:600;">Numéro qui paie</label>
            <input type="tel" name="telephone" id="telephone" required inputmode="numeric" maxlength="14" value="<?= e($contact) ?>" placeholder="ex. 0586189663" style="width:100%;padding:12px;margin:6px 0 14px;border:1px solid #ccc;border-radius:8px;">
            <div id="bloc-otp" style="display:none;">
                <label for="otp" style="font-weight:600;">Code de paiement Orange</label>
                <input type="text" name="otp" id="otp" inputmode="numeric" maxlength="8" autocomplete="off" placeholder="Code reçu" style="width:100%;padding:12px;margin:6px 0 4px;border:1px solid #ccc;border-radius:8px;">
                <small style="display:block;color:var(--texte-doux);margin-bottom:14px;">Composez <strong>#144*82#</strong> sur votre téléphone, choisissez l'option 2, puis saisissez ici le code reçu.</small>
            </div>
            <button type="submit" class="btn btn-wave" style="width:100%;">Payer <?= $montant ?> FCFA</button>
            <small style="display:block;text-align:center;color:var(--texte-doux);margin-top:10px;">Vous recevrez une demande de confirmation sur ce numéro.</small>
        </form>
<script>
(function () {
    var radios = document.querySelectorAll('input[name=operateur]'), bloc = document.getElementById('bloc-otp');
    if (!radios.length || !bloc) return;
    function maj() { var v = (document.querySelector('input[name=operateur]:checked') || {}).value; bloc.style.display = v === 'orange' ? 'block' : 'none'; }
    [].forEach.call(radios, function (r) { r.addEventListener('change', maj); }); maj();
})();
</script>
<?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
