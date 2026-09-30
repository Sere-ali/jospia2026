<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);
journalPreparer($pdo);

$du = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['du'] ?? '') ? $_GET['du'] : date('Y-m-d');
$au = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['au'] ?? '') ? $_GET['au'] : $du;
$qui = (int)($_GET['qui'] ?? 0);

$w = "created_at >= ? AND created_at < DATE_ADD(?, INTERVAL 1 DAY)";
$p = [$du, $au];
if ($qui > 0) { $w .= " AND compte_id = ?"; $p[] = $qui; }

$st = $pdo->prepare("SELECT * FROM journal_activite WHERE $w ORDER BY created_at DESC LIMIT 1000");
$st->execute($p);
$lignes = $st->fetchAll();

$st = $pdo->prepare("SELECT compte_id, MAX(nom) nom, MAX(role) role, COUNT(*) n, MAX(created_at) derniere FROM journal_activite WHERE $w GROUP BY compte_id ORDER BY n DESC");
$st->execute($p);
$resume = $st->fetchAll();

$acteurs = $pdo->query("SELECT compte_id, MAX(nom) nom FROM journal_activite WHERE compte_id IS NOT NULL GROUP BY compte_id ORDER BY nom")->fetchAll();
$roles = ['superadmin' => 'Super admin', 'admin' => 'Admin', 'finance' => 'Commission finance', 'scientifique' => 'Commission scientifique'];

$titrePage = "Activité journalière";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;"><span class="eyebrow">Super administrateur</span><h2>Activité journalière : qui a fait quoi</h2></div>

        <form method="get" class="carte" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
            <div class="form-group" style="margin:0;"><label>Du</label><input type="date" name="du" value="<?= e($du) ?>"></div>
            <div class="form-group" style="margin:0;"><label>Au</label><input type="date" name="au" value="<?= e($au) ?>"></div>
            <div class="form-group" style="margin:0;min-width:220px;"><label>Personne</label>
                <select name="qui"><option value="0">Tout le monde</option>
                    <?php foreach ($acteurs as $a): ?><option value="<?= (int)$a['compte_id'] ?>" <?= $qui === (int)$a['compte_id'] ? 'selected' : '' ?>><?= e($a['nom']) ?></option><?php endforeach; ?>
                </select></div>
            <button type="submit" class="btn btn-primaire">Afficher</button>
            <a href="<?= BASE_URL ?>/admin/activite" class="btn btn-outline">Aujourd'hui</a>
        </form>

        <div class="carte" style="margin-bottom:20px;overflow-x:auto;">
            <h3>Travaux de la période par personne</h3>
            <table>
                <thead><tr><th>Personne</th><th>Rôle</th><th>Nombre d'actions</th><th>Dernière activité</th></tr></thead>
                <tbody>
                <?php foreach ($resume as $r): ?>
                    <tr><td><strong><?= e($r['nom'] ?: '-') ?></strong></td><td><?= e($roles[$r['role']] ?? $r['role']) ?></td><td><?= (int)$r['n'] ?></td><td><?= date('d/m/Y H:i', strtotime($r['derniere'])) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$resume): ?><tr><td colspan="4" class="text-center">Aucune activité sur cette période.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="carte" style="overflow-x:auto;">
            <h3>Détail (<?= count($lignes) ?>)</h3>
            <table>
                <thead><tr><th>Date et heure</th><th>Qui</th><th>Rôle</th><th>A fait</th><th>Sur (qui / quoi)</th></tr></thead>
                <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
                        <td><?= e($l['nom'] ?: '-') ?></td>
                        <td><?= e($roles[$l['role']] ?? $l['role']) ?></td>
                        <td><?= e($l['action']) ?></td>
                        <td><?= e($l['cible']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$lignes): ?><tr><td colspan="5" class="text-center">Rien à afficher.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
