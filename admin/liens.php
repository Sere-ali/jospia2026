<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);
// Liens courts à partager : créés uniquement par le super administrateur
$succesLien = null; $liensCourts = [];
{
    liensCourtsPreparer($pdo);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lien_action'], $_POST['cible']) && isset(LIENS_COURTS[$_POST['cible']])) {
        $cible = $_POST['cible'];
        if ($_POST['lien_action'] === 'generer') {
            $pdo->prepare("DELETE FROM liens_courts WHERE cible = ?")->execute([$cible]);
            for ($t = 0; $t < 5; $t++) {
                try { $pdo->prepare("INSERT INTO liens_courts (cible, code) VALUES (?, ?)")->execute([$cible, codeCourt(LIENS_COURTS[$cible][2])]); break; } catch (Throwable $e) {}
            }
            $succesLien = "Lien prêt : copiez-le puis envoyez-le.";
        } elseif ($_POST['lien_action'] === 'desactiver') {
            $pdo->prepare("DELETE FROM liens_courts WHERE cible = ?")->execute([$cible]);
            $succesLien = "Lien désactivé : il ne fonctionne plus.";
        }
    }
    foreach ($pdo->query("SELECT cible, code FROM liens_courts")->fetchAll() as $r) { $liensCourts[$r['cible']] = $r['code']; }
}

$titrePage = "Liens";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Super Administrateur</span><h2>Liens</h2></div>
        <div class="carte" style="margin-bottom:24px;">
            <h3>Liens à envoyer</h3>
            <?php if ($succesLien): ?><div class="alert alert-succes"><?= e($succesLien) ?></div><?php endif; ?>
            <p>Liens courts créés et gérés uniquement par le super administrateur. Le lien « Niveaux selon les notes » permet à la commission scientifique de remplir la page sans compte : ne l'envoyez qu'à elle.</p>
            <?php foreach (LIENS_COURTS as $cle => $def): $code = $liensCourts[$cle] ?? ''; ?>
            <div style="border-top:1px solid #e5e5e5;padding:12px 0;">
                <strong><?= e($def[0]) ?></strong>
                <?php if ($code !== ''): ?>
                    <input type="text" readonly value="<?= e(urlSite() . '/l/' . $code) ?>" onclick="this.select()" style="font-family:monospace;margin:6px 0;">
                    <form method="post" data-no-ajax style="display:flex;gap:8px;flex-wrap:wrap;"><input type="hidden" name="cible" value="<?= e($cle) ?>">
                        <button type="button" class="btn btn-or btn-sm" onclick="navigator.clipboard&&navigator.clipboard.writeText(this.form.previousElementSibling.value);this.textContent='✔ Copié'">📋 Copier</button>
                        <button class="btn btn-outline btn-sm" name="lien_action" value="generer" onclick="return confirm('Créer un nouveau lien ? L\'ancien ne fonctionnera plus.')">🔄 Nouveau</button>
                        <button class="btn btn-danger btn-sm" name="lien_action" value="desactiver" onclick="return confirm('Désactiver ce lien ?')">⛔ Désactiver</button>
                    </form>
                <?php else: ?>
                    <form method="post" data-no-ajax style="margin-top:6px;"><input type="hidden" name="cible" value="<?= e($cle) ?>"><button class="btn btn-primaire btn-sm" name="lien_action" value="generer">🔗 Créer le lien</button></form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
