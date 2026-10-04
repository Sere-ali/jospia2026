<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$dortoir = $_GET['dortoir'] ?? '';
$niveau = $_GET['niveau'] ?? '';
$sql = "SELECT id, nom_prenoms, dortoir, niveau_affecte, matricule FROM seminaristes WHERE 1=1";
$params = [];
if ($dortoir !== '') { $sql .= " AND dortoir = ?"; $params[] = $dortoir; }
if ($niveau !== '') { $sql .= " AND niveau_affecte = ?"; $params[] = $niveau; }
$sql .= " ORDER BY dortoir, nom_prenoms";
$st = $pdo->prepare($sql);
$st->execute($params);
$liste = $st->fetchAll();
$dortoirs = $pdo->query("SELECT DISTINCT dortoir FROM seminaristes WHERE dortoir IS NOT NULL AND dortoir <> '' ORDER BY dortoir")->fetchAll(PDO::FETCH_COLUMN);
$niveaux = listeNiveaux();
$nbPages = (int)ceil(count($liste) / 4);
$qs = http_build_query(['type' => 'badge_sem', 'tous' => 1, 'dortoir' => $dortoir, 'niveau' => $niveau]);

$titrePage = "Badges des séminaristes (PDF)";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-tete" style="text-align:left;"><h2>Badges des séminaristes - PDF A4 (4 par page)</h2></div>
        <div class="carte" style="margin-bottom:18px;">
            <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <select name="dortoir" onchange="this.form.submit()">
                    <option value="">Tous les dortoirs</option>
                    <?php foreach ($dortoirs as $d): ?><option value="<?= e($d) ?>" <?= $dortoir === $d ? 'selected' : '' ?>><?= e($d) ?></option><?php endforeach; ?>
                </select>
                <select name="niveau" onchange="this.form.submit()">
                    <option value="">Tous les niveaux</option>
                    <?php foreach ($niveaux as $n): ?><option value="<?= e($n) ?>" <?= $niveau === $n ? 'selected' : '' ?>><?= e($n) ?></option><?php endforeach; ?>
                </select>
                <a href="<?= BASE_URL ?>/admin/pdf?<?= e($qs) ?>" class="btn btn-primaire">⬇️ Télécharger <?= count($liste) ?> badge(s) en PDF - <?= $nbPages ?> page(s) A4</a>
            </form>
            <p style="color:var(--texte-doux);margin:10px 0 0;">Le fichier PDF est prêt à imprimer : 4 badges par page A4, avec repères de découpe. Avec beaucoup de badges, la génération peut prendre un peu de temps.</p>
        </div>
        <div class="carte" style="overflow-x:auto;">
            <table>
                <thead><tr><th>Matricule</th><th>Nom et prénoms</th><th>Dortoir</th><th>Niveau</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($liste as $s): ?>
                    <tr>
                        <td class="mono"><?= e($s['matricule']) ?></td><td><?= e($s['nom_prenoms']) ?></td><td><?= e($s['dortoir']) ?></td><td><?= e($s['niveau_affecte'] ?: 'Non affecté') ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/pdf?type=badge_sem&id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline">⬇️ PDF</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$liste): ?><tr><td colspan="5" class="text-center">Aucun badge.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
