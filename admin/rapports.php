<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
date_default_timezone_set('Africa/Abidjan');
rapportsPreparer($pdo);
$titrePage = "Rapports journaliers";
$erreurs = []; $succes = null;
$toutes = listeCommissions();
$commission = $_GET['commission'] ?? '';
if ($commission !== '' && !in_array($commission, $toutes, true)) { $commission = ''; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'supprimer' && estSuperAdmin()) {
    $id = (int)($_POST['id'] ?? 0);
    $pdo->prepare("DELETE FROM rapports_journaliers WHERE id = ?")->execute([$id]);
    journaliser($pdo, 'Rapport supprimé', '#' . $id);
    $succes = "Rapport supprimé.";
}

$resume = [];
foreach ($pdo->query("SELECT commission, COUNT(*) total, MAX(date_rapport) dernier FROM rapports_journaliers GROUP BY commission")->fetchAll() as $r) { $resume[$r['commission']] = $r; }
$aujourdhui = date('Y-m-d');
$faits = [];
$st = $pdo->prepare("SELECT DISTINCT commission FROM rapports_journaliers WHERE date_rapport = ?"); $st->execute([$aujourdhui]);
foreach ($st->fetchAll() as $r) { $faits[$r['commission']] = true; }
$jourChoisi = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['jour'] ?? '') ? $_GET['jour'] : '';

$rapports = [];
if ($commission !== '') {
    $st = $pdo->prepare("SELECT * FROM rapports_journaliers WHERE commission = ?" . ($jourChoisi ? " AND date_rapport = ?" : "") . " ORDER BY date_rapport DESC, id DESC LIMIT 300");
    $st->execute($jourChoisi ? [$commission, $jourChoisi] : [$commission]);
    $rapports = $st->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
$q = function ($com, $jour = '') { return BASE_URL . '/admin/rapport_pdf?commission=' . urlencode($com) . ($jour ? '&jour=' . urlencode($jour) : ''); };
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Back-office</span><h2>📝 Rapports journaliers des commissions</h2></div>
        <p class="no-print" style="text-align:center;margin-bottom:14px;"><a href="<?= BASE_URL ?>/espace/rapport" class="btn btn-outline btn-sm">✍️ Rédiger le rapport de l'administration</a></p>
        <?php foreach ($erreurs as $err): ?><div class="alert alert-erreur"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <?php if ($commission === ''): ?>
            <p style="text-align:center;color:var(--texte-doux);margin-bottom:16px;">Les rapports sont saisis par chaque commission depuis son espace. Ici, vous les consultez et les téléchargez en PDF.</p>
            <form method="get" class="no-print carte" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:center;margin-bottom:20px;">
                <strong>Tous les rapports d'un jour :</strong>
                <input type="date" name="jour" value="<?= e($jourChoisi ?: $aujourdhui) ?>" style="max-width:200px;" id="jour-global">
                <a href="#" class="btn btn-primaire btn-sm" onclick="this.href='<?= BASE_URL ?>/admin/rapport_pdf?jour='+document.getElementById('jour-global').value;">📄 Télécharger en PDF</a>
                <a href="<?= BASE_URL ?>/admin/rapport_pdf" class="btn btn-outline btn-sm">📄 Tous les rapports (PDF)</a>
            </form>
            <div class="grid grid-3" data-live="cartes">
                <?php foreach ($toutes as $c): $r = $resume[$c] ?? null; $fait = !empty($faits[$c]); ?>
                    <div class="carte">
                        <h3><?= e($c) ?></h3>
                        <?php if (nomCommissionComplet($c) !== $c): ?><p style="color:var(--texte-doux);font-size:.88rem;margin:0 0 8px;"><?= e(nomCommissionComplet($c)) ?></p><?php endif; ?>
                        <p><?= $fait ? '<span class="pill pill-vert">✔ Rapport du jour déposé</span>' : '<span class="pill pill-or">⏳ Rapport du jour à faire</span>' ?></p>
                        <p style="font-size:.85rem;color:var(--texte-doux);"><?= (int)($r['total'] ?? 0) ?> rapport(s)<?= !empty($r['dernier']) ? ' · dernier le ' . e(date('d/m/Y', strtotime($r['dernier']))) : '' ?></p>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="?commission=<?= urlencode($c) ?>" class="btn btn-primaire btn-sm">Consulter</a>
                            <?php if (!empty($r['total'])): ?><a href="<?= e($q($c)) ?>" class="btn btn-outline btn-sm">📄 PDF</a><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-print" style="margin-bottom:14px;display:flex;gap:8px;flex-wrap:wrap;">
                <a href="<?= BASE_URL ?>/admin/rapports" class="btn btn-outline btn-sm">&larr; Toutes les commissions</a>
                <?php if ($rapports): ?><a href="<?= e($q($commission, $jourChoisi)) ?>" class="btn btn-primaire btn-sm">📄 Télécharger en PDF<?= $jourChoisi ? ' (' . e(date('d/m/Y', strtotime($jourChoisi))) . ')' : '' ?></a><?php endif; ?>
            </p>
            <h3 style="text-align:center;margin-bottom:14px;">Commission <?= e($commission) ?><?php if (nomCommissionComplet($commission) !== $commission): ?> <small style="color:var(--texte-doux);font-weight:400;">- <?= e(nomCommissionComplet($commission)) ?></small><?php endif; ?></h3>
            <form method="get" class="no-print" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <input type="hidden" name="commission" value="<?= e($commission) ?>">
                <input type="date" name="jour" value="<?= e($jourChoisi) ?>" onchange="this.form.submit()" style="max-width:200px;">
                <?php if ($jourChoisi): ?><a href="?commission=<?= urlencode($commission) ?>" class="btn btn-outline btn-sm">Tous les jours</a><?php endif; ?>
            </form>
            <div data-live="rapports">
                <?php foreach ($rapports as $r):
                    $act = '<a href="' . BASE_URL . '/admin/rapport_pdf?id=' . (int)$r['id'] . '" class="btn btn-sm btn-outline">📄 PDF de ce rapport</a>';
                    if (estSuperAdmin()) $act .= '<form method="post" onsubmit="return confirm(\'Supprimer ce rapport ?\');" style="display:inline;"><input type="hidden" name="action" value="supprimer"><input type="hidden" name="id" value="' . (int)$r['id'] . '"><button class="btn btn-sm btn-danger">🗑️ Supprimer</button></form>';
                    echo lettreRapportHtml($r, $act);
                endforeach; ?>
                <?php if (!$rapports): ?><div class="carte" style="text-align:center;color:var(--texte-doux);">Aucun rapport pour le moment.</div><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
