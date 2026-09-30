<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['seminariste']);
$u = utilisateurCourant();

$stmt = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$stmt->execute([$u['seminariste_id']]);
$seminariste = $stmt->fetch();
if (!$seminariste) { die("Fiche introuvable."); }

$erreurs = [];
$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commission = $_POST['commission'] ?? '';
    $contenu = trim($_POST['contenu'] ?? '');

    if (!in_array($commission, listeCommissions(), true)) $erreurs[] = "Veuillez choisir une commission valide.";
    if ($contenu === '') $erreurs[] = "Veuillez rédiger votre critique avant d'envoyer.";
    elseif (strlen($contenu) > 3000) $erreurs[] = "Votre message est trop long.";

    if (empty($erreurs)) {
        $pdo->prepare("INSERT INTO critiques (seminariste_id, commission, contenu) VALUES (?,?,?)")
            ->execute([$seminariste['id'], $commission, $contenu]);
        $succes = "Votre critique a été envoyée à la commission $commission. Merci pour votre retour !";
    }
}

$mesCritiques = $pdo->prepare("SELECT * FROM critiques WHERE seminariste_id = ? ORDER BY created_at DESC");
$mesCritiques->execute([$seminariste['id']]);
$mesCritiques = $mesCritiques->fetchAll();

$titrePage = "Critiquer une commission";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container form-wrap">
        <a href="<?= BASE_URL ?>/espace/fiche.php" class="btn btn-outline btn-sm">&larr; Retour à mon espace</a>

        <div class="section-titre" style="text-align:left;margin-top:16px;">
            <span class="eyebrow">Votre avis compte</span>
            <h2>Critiquer une commission</h2>
            <p style="color:var(--texte-doux);">Donnez votre avis sur le travail d'une commission durant les JOSPIA (organisation, accueil, ponctualité, etc.). Votre message sera transmis directement aux membres de la commission concernée.</p>
        </div>

        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <form method="post">
            <fieldset>
                <legend>Nouvelle critique</legend>
                <div class="form-group">
                    <label>Commission concernée <span class="req">*</span></label>
                    <select name="commission" required>
                        <option value="">— Choisir une commission —</option>
                        <?php foreach (listeCommissions() as $c): ?>
                            <option value="<?= e($c) ?>" <?= (($_POST['commission'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Votre message <span class="req">*</span></label>
                    <textarea name="contenu" rows="5" maxlength="2000" required placeholder="Décrivez votre remarque de façon claire et constructive..."><?= e($_POST['contenu'] ?? '') ?></textarea>
                    <div class="help-text">2000 caractères maximum.</div>
                </div>
            </fieldset>
            <button type="submit" class="btn btn-primaire btn-block">Envoyer ma critique</button>
        </form>

        <?php if ($mesCritiques): ?>
        <div class="carte" style="margin-top:30px;">
            <h3>Mes critiques envoyées</h3>
            <div class="table-wrap" style="box-shadow:none;">
                <table>
                    <thead><tr><th>Date</th><th>Commission</th><th>Message</th></tr></thead>
                    <tbody>
                    <?php foreach ($mesCritiques as $c): ?>
                        <tr>
                            <td><?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?></td>
                            <td><span class="pill pill-vert"><?= e($c['commission']) ?></span></td>
                            <td><?= nl2br(e($c['contenu'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
