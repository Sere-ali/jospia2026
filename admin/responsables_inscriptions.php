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
        <table class="table" style="width:100%;"><thead><tr><th>Photo</th><th>Nom et prénom(s)</th><th>Commission</th><th>Profil</th><th>Depuis</th><th>Niveau d'études / fonction</th><th></th></tr></thead><tbody>
        <?php foreach ($liste as $r): ?><tr>
            <td><?php if ($r['photo']): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e($r['photo']) ?>" alt="" style="width:46px;height:46px;border-radius:8px;object-fit:cover;"><?php endif; ?></td>
            <td><strong><?= e($r['nom_prenoms']) ?></strong></td><td><?= e($r['commission']) ?></td><td><?= e($r['profil']) ?></td><td><?= e($r['annee_debut']) ?></td><td><?= e($r['niveau_fonction']) ?></td>
            <td><form method="post" onsubmit="return confirm('Supprimer cette fiche ?')"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="jeton" value="<?= e(resJeton()) ?>"><button class="btn btn-outline btn-sm" type="submit">Supprimer</button></form></td>
        </tr><?php endforeach; ?></tbody></table><?php endif; ?>
    </div>
</div></section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
