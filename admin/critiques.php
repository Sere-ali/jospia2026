<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$filtreCommission = $_GET['commission'] ?? '';
$sql = "SELECT c.*, s.nom_prenoms, s.matricule FROM critiques c
        JOIN seminaristes s ON s.id = c.seminariste_id WHERE 1=1";
$params = [];
if ($filtreCommission !== '') {
    $sql .= " AND c.commission = ?";
    $params[] = $filtreCommission;
}
$sql .= " ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$critiques = $stmt->fetchAll();

$titrePage = "Critiques des séminaristes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;">
            <h2>Critiques envoyées par les séminaristes (<?= count($critiques) ?>)</h2>
            <p style="color:var(--texte-doux);">Vue d'ensemble pour le comité d'organisation. Les membres de commission ne voient que les critiques de leur propre commission (sans le nom de l'auteur).</p>
        </div>

        <form method="get" class="carte" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
            <div class="form-group" style="min-width:220px;margin:0;">
                <label>Filtrer par commission</label>
                <select name="commission" onchange="this.form.submit()">
                    <option value="">Toutes les commissions</option>
                    <?php foreach (listeCommissions() as $c): ?>
                        <option value="<?= e($c) ?>" <?= $filtreCommission === $c ? 'selected' : '' ?>><?= e($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Commission</th><th>Séminariste (matricule)</th><th>Message</th></tr></thead>
                <tbody>
                <?php foreach ($critiques as $c): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?></td>
                        <td><span class="pill pill-vert"><?= e($c['commission']) ?></span></td>
                        <td><?= e($c['nom_prenoms']) ?> <span class="mono" style="color:var(--texte-doux);">(<?= e($c['matricule']) ?>)</span></td>
                        <td><?= nl2br(e($c['contenu'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$critiques): ?><tr><td colspan="4">Aucune critique pour le moment.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
