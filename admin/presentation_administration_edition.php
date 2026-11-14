<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
require_once __DIR__ . '/../includes/ressources_sci.php';
require_once __DIR__ . '/../includes/presentation_admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals(resJeton(), (string)($_POST['jeton'] ?? ''))) { http_response_code(400); exit('Formulaire expiré, rechargez la page.'); }
    presAdminEnregistrer($_POST);
    $_SESSION['pres_admin_ok'] = true;
    redirect(BASE_URL . '/admin/presentation_administration_edition');
}
$ok = !empty($_SESSION['pres_admin_ok']); unset($_SESSION['pres_admin_ok']);
$contenu = presAdminLire();

$titrePage = "Présentation — contenu";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:860px;">
        <div class="section-titre" style="text-align:left;">
            <h2>Présentation de la commission Administration</h2>
            <p style="color:var(--texte-doux);">Bataille de Kirina : saisissez ici les travaux effectués, non effectués et les difficultés. <strong>Un élément par ligne.</strong> Les rubriques laissées vides ne s'affichent pas dans le diaporama.</p>
        </div>
        <?php if ($ok): ?><div class="carte" style="border-left:5px solid var(--vert,#0B8A4E);margin-bottom:16px;">Contenu enregistré. <a href="<?= BASE_URL ?>/admin/presentation_administration">Voir la présentation</a></div><?php endif; ?>
        <form method="post" class="carte">
            <input type="hidden" name="jeton" value="<?= e(resJeton()) ?>">
            <?php foreach (PRES_ADMIN_RUBRIQUES as $k => $def): ?>
                <div style="margin-bottom:18px;">
                    <label for="<?= $k ?>"><strong><?= e($def[1]) ?></strong></label>
                    <div style="color:var(--texte-doux);font-size:.9em;margin:2px 0 6px;"><?= e($def[2]) ?></div>
                    <textarea id="<?= $k ?>" name="<?= $k ?>" rows="8" style="width:100%;"><?= e($contenu[$k]) ?></textarea>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primaire">Enregistrer</button>
            <a href="<?= BASE_URL ?>/admin/presentation_administration" class="btn btn-outline">Ouvrir la présentation</a>
            <a href="<?= BASE_URL ?>/admin/administration" class="btn btn-outline">Retour</a>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
