<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);

$filtreNiveau = $_GET['niveau'] ?? '';
$sql = "SELECT * FROM seminaristes WHERE 1=1";
$params = [];
if ($filtreNiveau !== '') {
    $sql .= " AND niveau_affecte = ?";
    $params[] = $filtreNiveau;
}
$sql .= " ORDER BY niveau_affecte, nom_prenoms";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$seminaristesListe = $stmt->fetchAll();
$pages = array_chunk($seminaristesListe, 1);
$niveauxListe = ['Pépinière', 'Primaire', 'Secondaire', 'Universitaire', 'Leader'];

$titrePage = "Impression des bulletins";
require_once __DIR__ . '/../includes/header.php';
?>
<section class="section">
    <div class="container">
        <div class="no-print" style="text-align:center;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/notes" class="btn btn-outline btn-sm">&larr; Retour à la saisie des notes</a>
            <form method="get" style="display:inline-flex;gap:8px;">
                <select name="niveau" onchange="this.form.submit()">
                    <option value="">Tous les niveaux (<?= count($seminaristesListe) ?> bulletins)</option>
                    <?php foreach ($niveauxListe as $n): ?>
                        <option value="<?= e($n) ?>" <?= $filtreNiveau === $n ? 'selected' : '' ?>><?= e($n) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="<?= BASE_URL ?>/admin/bulletin_pdf?tous=1&niveau=<?= urlencode($filtreNiveau) ?>" class="btn btn-primaire">⬇️ Télécharger en PDF - <?= count($seminaristesListe) ?> bulletin(s), 1 par page</a>
        </div>

        <?php if (!$seminaristesListe): ?>
            <div class="alert alert-info">Aucun bulletin à imprimer pour ce filtre.</div>
        <?php endif; ?>

        <?php foreach ($pages as $page): ?>
            <div class="page-bulletins">
                <?php foreach ($page as $seminariste): ?>
                    <?php require __DIR__ . '/../includes/bulletin_rendu.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
