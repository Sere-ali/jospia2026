<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (estScientifique()) {
        $v = ($_POST['ouvrir'] ?? '0') === '1' ? '1' : '0';
        $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
        $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES ('test_ouvert', ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")->execute([$v]);
        $_SESSION['flash_succes'] = $v === '1' ? "Test d'entrée déverrouillé." : "Test d'entrée verrouillé.";
    }
    $retour = ($_POST['retour'] ?? '') === 'dashboard' ? '/admin/dashboard' : '/admin/test_entree';
    redirect($retour);
}

$nbTotal = (int)$pdo->query("SELECT COUNT(*) FROM seminaristes WHERE dortoir IS NULL OR dortoir <> 'Pépinière'")->fetchColumn();
$nbFaits = (int)$pdo->query("SELECT COUNT(*) FROM seminaristes WHERE test_complete = 1")->fetchColumn();
$nbQuestions = (int)$pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$resultats = $pdo->query("SELECT id, matricule, nom_prenoms, note_test, niveau_affecte FROM seminaristes WHERE test_complete = 1 ORDER BY note_test DESC, nom_prenoms")->fetchAll();

$titrePage = "Test d'entrée";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow">Commission scientifique</span>
            <h2>Test d'entrée</h2>
            <p>Durée : 20 minutes, soumission automatique à la fin du temps. Noté sur 20.</p>
        </div>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>

        <?php if (estScientifique()): echo blocTestEntree($pdo); else: ?>
            <div class="carte" style="margin-bottom:24px;"><h3><?= testOuvert($pdo) ? "🔓 Test déverrouillé" : "🔒 Test verrouillé" ?></h3><p>Seuls la commission scientifique et le super administrateur peuvent le verrouiller ou le déverrouiller.</p></div>
        <?php endif; ?>

        <div class="grid grid-3" style="margin-bottom:24px;">
            <div class="carte stat-card"><div class="chiffre"><?= $nbFaits ?> / <?= $nbTotal ?></div><div class="label">Tests composés</div></div>
            <div class="carte stat-card"><div class="chiffre"><?= $nbQuestions ?></div><div class="label">Questions en banque</div></div>
            <div class="carte stat-card"><div class="chiffre">20 min</div><div class="label">Durée du test</div></div>
        </div>

        <?php if (estScientifique()): ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;">
            <a href="<?= BASE_URL ?>/admin/questions" class="btn btn-primaire btn-sm">📝 Questions du test</a>
            <a href="<?= BASE_URL ?>/admin/config_quiz" class="btn btn-primaire btn-sm">⚙️ Config Quiz (6 banques)</a>
        </div>
        <?php endif; ?>

        <div class="carte" style="overflow-x:auto;">
            <h3>Résultats</h3>
            <table>
                <thead><tr><th>Matricule</th><th>Nom et prénoms</th><th>Note /20</th><th>Niveau</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($resultats as $r): ?>
                    <tr>
                        <td class="mono"><?= e($r['matricule']) ?></td><td><?= e($r['nom_prenoms']) ?></td><td><?= e($r['note_test']) ?></td><td><?= e($r['niveau_affecte']) ?></td>
                        <td><a href="<?= BASE_URL ?>/admin/correction?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline">🔍 Correction</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$resultats): ?><tr><td colspan="5" class="text-center">Aucun test composé pour le moment.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
