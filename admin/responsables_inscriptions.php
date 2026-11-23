<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin', 'mg']);
require_once __DIR__ . '/../includes/ressources_sci.php';
require_once __DIR__ . '/../includes/responsables_pres.php';
respPresPreparer($pdo);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer') {
    if (!hash_equals(resJeton(), (string)($_POST['jeton'] ?? ''))) { http_response_code(400); exit('Formulaire expiré, rechargez la page.'); }
    $pdo->prepare("DELETE FROM presentation_responsables WHERE id = ?")->execute([(int)($_POST['id'] ?? 0)]);
    redirect(BASE_URL . '/admin/responsables_inscriptions');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'modifier') {
    if (!hash_equals(resJeton(), (string)($_POST['jeton'] ?? ''))) { http_response_code(400); exit('Formulaire expiré, rechargez la page.'); }
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare("SELECT photo FROM presentation_responsables WHERE id = ?"); $st->execute([$id]); $photo = (string)$st->fetchColumn();
    if (!empty($_FILES['photo']['name']) && ($np = uploadPhoto($_FILES['photo']))) $photo = $np;
    $annee = (int)($_POST['annee_debut'] ?? 0);
    try {
        $pdo->prepare("UPDATE presentation_responsables SET commission = ?, nom_prenoms = ?, annee_debut = ?, niveau_fonction = ?, photo = ? WHERE id = ?")
            ->execute([in_array($_POST['commission'] ?? '', listeCommissions(), true) ? $_POST['commission'] : listeCommissions()[0], mb_substr(trim($_POST['nom_prenoms'] ?? ''), 0, 190), $annee >= 1990 ? $annee : null, mb_substr(trim($_POST['niveau_fonction'] ?? ''), 0, 255), $photo, $id]);
    } catch (Throwable $e) { }
    redirect(BASE_URL . '/admin/responsables_inscriptions');
}
$editId = (int)($_GET['modifier'] ?? 0);
$liste = respPresListe($pdo);
$lien = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http')) . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/inscription_responsable';
$titrePage = "Responsables — fiches de présentation";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section"><div class="container">
    <div class="section-titre" style="text-align:left;"><h2>Présentation des responsables</h2>
        <p style="color:var(--texte-doux);">Les responsables de commission remplissent leur fiche avec le lien ci-dessous ; leur présentation se construit automatiquement.</p></div>
    <div class="carte" style="margin-bottom:18px;">
        <h3>Lien du formulaire à envoyer aux responsables</h3>
        <p><input type="text" readonly id="lien" value="<?= e($lien) ?>" style="width:100%;" onclick="this.select()"></p>
        <button type="button" class="btn btn-outline btn-sm" onclick="var i=document.getElementById('lien');i.select();navigator.clipboard&&navigator.clipboard.writeText(i.value);this.textContent='Lien copié ✓'">Copier le lien</button>
        <a href="<?= BASE_URL ?>/admin/presentation_responsables" class="btn btn-primaire btn-sm">Ouvrir la présentation</a>
    </div>
    <div class="carte" style="overflow-x:auto;">
        <h3><?= count($liste) ?> fiche(s) reçue(s)</h3>
        <?php if (!$liste): ?><p>Aucune fiche pour le moment.</p><?php else: ?>
        <table class="table" style="width:100%;"><thead><tr><th>Photo</th><th>Nom et prénom(s)</th><th>Commission</th><th>Depuis</th><th>Niveau d'études / fonction</th><th></th></tr></thead><tbody>
        <?php foreach ($liste as $r): if ($editId === (int)$r['id']): ?><tr><td colspan="6">
            <form method="post" enctype="multipart/form-data" class="form-pro" style="display:grid;gap:8px;max-width:640px;">
                <input type="hidden" name="action" value="modifier"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="jeton" value="<?= e(resJeton()) ?>">
                <label>Nom et prénom(s)<input type="text" name="nom_prenoms" value="<?= e($r['nom_prenoms']) ?>" required></label>
                <label>Commission<select name="commission"><?php foreach (listeCommissions() as $c): ?><option <?= $c === $r['commission'] ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?></select></label>
                <label>Année de début<input type="number" name="annee_debut" min="1990" max="<?= (int)date('Y') ?>" value="<?= e($r['annee_debut']) ?>"></label>
                <label>Niveau d'études / fonction<textarea name="niveau_fonction" rows="3"><?= e($r['niveau_fonction']) ?></textarea></label>
                <label>Nouvelle photo (facultatif)<input type="file" name="photo" accept="image/*"></label>
                <div><button class="btn btn-primaire btn-sm" type="submit">Enregistrer</button> <a class="btn btn-outline btn-sm" href="<?= BASE_URL ?>/admin/responsables_inscriptions">Annuler</a></div>
            </form></td></tr>
        <?php continue; endif; ?><tr>
            <td><?php if ($r['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($r['photo']) ?>" alt="" style="width:46px;height:46px;border-radius:8px;object-fit:cover;"><?php endif; ?></td>
            <td><strong><?= e($r['nom_prenoms']) ?></strong></td><td><?= e($r['commission']) ?></td><td><?= e($r['annee_debut']) ?></td><td><?= e($r['niveau_fonction']) ?></td>
            <td style="white-space:nowrap;"><a class="btn btn-outline btn-sm" href="?modifier=<?= (int)$r['id'] ?>">Modifier</a> <form method="post" style="display:inline" onsubmit="return confirm('Supprimer cette fiche ?')"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="jeton" value="<?= e(resJeton()) ?>"><button class="btn btn-outline btn-sm" type="submit">Supprimer</button></form></td>
        </tr><?php endforeach; ?></tbody></table><?php endif; ?>
    </div>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
