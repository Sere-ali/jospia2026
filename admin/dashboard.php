<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

// Lien secret « niveaux selon les notes » : géré uniquement par le super administrateur
$succesLien = null;
if (estSuperAdmin()) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lien_action'])) {
        if ($_POST['lien_action'] === 'generer') {
            $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES ('lien_niveaux', ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")->execute([bin2hex(random_bytes(16))]);
            $succesLien = "Lien créé : copiez-le puis envoyez-le à la commission scientifique.";
        } elseif ($_POST['lien_action'] === 'desactiver') {
            $pdo->exec("DELETE FROM parametres WHERE cle = 'lien_niveaux'");
            $succesLien = "Lien désactivé : il ne fonctionne plus.";
        }
    }
    $tokenLien = (string)$pdo->query("SELECT valeur FROM parametres WHERE cle = 'lien_niveaux'")->fetchColumn();
}

$nbMembres = $pdo->query("SELECT COUNT(*) FROM membres_commission")->fetchColumn();
$nbSeminaristes = $pdo->query("SELECT COUNT(*) FROM seminaristes")->fetchColumn();
$nbTestsFaits = $pdo->query("SELECT COUNT(*) FROM seminaristes WHERE test_complete = 1")->fetchColumn();
$nbHommes = $pdo->query("SELECT COUNT(*) FROM seminaristes WHERE genre='Masculin'")->fetchColumn();
$nbFemmes = $pdo->query("SELECT COUNT(*) FROM seminaristes WHERE genre='Féminin'")->fetchColumn();

$parCommission = $pdo->query("SELECT commission, COUNT(*) n FROM membres_commission GROUP BY commission ORDER BY n DESC")->fetchAll();
$parNiveau = $pdo->query("SELECT niveau_affecte, COUNT(*) n FROM seminaristes WHERE niveau_affecte IS NOT NULL GROUP BY niveau_affecte")->fetchAll();
$parAnyama = $pdo->query("SELECT anyama, COUNT(*) n FROM seminaristes GROUP BY anyama ORDER BY n DESC")->fetchAll();
$parSection = $pdo->query("SELECT section, COUNT(*) n FROM seminaristes GROUP BY section ORDER BY n DESC")->fetchAll();

// Statistiques des paiements Wave
$statsPaiements = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'validé' THEN 1 ELSE 0 END) as valides,
        SUM(CASE WHEN statut = 'en attente' THEN 1 ELSE 0 END) as en_attente
    FROM paiements
")->fetch();
$nbPaiementsValides = (int)$statsPaiements['valides'];
$nbPaiementsAttente = (int)$statsPaiements['en_attente'];
$montantTotalEncaisse = $nbPaiementsValides * FRAIS_PARTICIPATION;

$titrePage = "Tableau de bord";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre">
            <span class="eyebrow"><?= estSuperAdmin() ? 'Super Administrateur' : 'Administrateur' ?></span>
            <h2>Tableau de bord - <?= EVENT_NAME ?></h2>
        </div>

        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>
        <?php if (estSuperAdmin()): ?>
        <div class="carte" style="margin-bottom:24px;">
            <h3>🔗 Lien à envoyer à la commission scientifique</h3>
            <?php if ($succesLien): ?><div class="alert alert-succes"><?= e($succesLien) ?></div><?php endif; ?>
            <p>Avec ce lien, la commission scientifique remplit la page « Niveaux selon les notes » sans compte. Réservé au super administrateur : ne l'envoyez qu'à la commission scientifique.</p>
            <?php if ($tokenLien !== ''): ?>
                <input type="text" readonly value="<?= e(urlSite() . '/niveaux?t=' . $tokenLien) ?>" onclick="this.select()" style="font-family:monospace;">
                <form method="post" data-no-ajax style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="button" class="btn btn-or btn-sm" onclick="navigator.clipboard&&navigator.clipboard.writeText(this.form.previousElementSibling.value);this.textContent='✔ Copié'">📋 Copier le lien</button>
                    <button class="btn btn-outline btn-sm" name="lien_action" value="generer" onclick="return confirm('Créer un nouveau lien ? L\'ancien ne fonctionnera plus.')">🔄 Nouveau lien</button>
                    <button class="btn btn-danger btn-sm" name="lien_action" value="desactiver" onclick="return confirm('Désactiver le lien ?')">⛔ Désactiver</button>
                </form>
            <?php else: ?>
                <form method="post" data-no-ajax><button class="btn btn-primaire btn-sm" name="lien_action" value="generer">🔗 Créer le lien</button></form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if (estSuperAdmin()) echo blocTestEntree($pdo); ?>

        <div class="grid grid-3" style="margin-bottom:30px;">
            <div class="carte stat-card"><div class="chiffre"><?= $nbMembres ?></div><div class="label">Membres de commission</div></div>
            <div class="carte stat-card"><div class="chiffre"><?= $nbSeminaristes ?></div><div class="label">Séminaristes inscrits</div></div>
            <div class="carte stat-card"><div class="chiffre"><?= $nbTestsFaits ?> / <?= $nbSeminaristes ?></div><div class="label">Tests d'entrée réalisés</div></div>
        </div>

        <div class="grid grid-3" style="margin-bottom:30px;">
            <div class="carte stat-card"><div class="chiffre"><?= $nbHommes ?></div><div class="label">Séminaristes hommes</div></div>
            <div class="carte stat-card"><div class="chiffre"><?= $nbFemmes ?></div><div class="label">Séminaristes femmes</div></div>
            <div class="carte stat-card"><div class="chiffre">≤ <?= AGE_PEPINIERE_SEUIL ?> ans</div><div class="label">Seuil dortoir Pépinière</div></div>
        </div>

        <div class="section-titre" style="margin-top: 40px;">
            <h3>Statistiques Financières (Wave)</h3>
        </div>
        <div class="grid grid-3" style="margin-bottom:30px;">
            <div class="carte stat-card" style="border-left: 4px solid #1cc6f4;"><div class="chiffre" style="color: #1cc6f4;"><?= number_format($montantTotalEncaisse, 0, ',', ' ') ?> F</div><div class="label">Total Encaissé</div></div>
            <div class="carte stat-card" style="border-left: 4px solid var(--couleur-succes);"><div class="chiffre" style="color: var(--couleur-succes);"><?= $nbPaiementsValides ?></div><div class="label">Paiements Validés</div></div>
            <div class="carte stat-card" style="border-left: 4px solid #f39c12;"><div class="chiffre" style="color: #f39c12;"><?= $nbPaiementsAttente ?></div><div class="label">Paiements en Attente</div></div>
        </div>

        <?php require __DIR__ . '/../includes/graphiques_commissions.php'; ?>

        <div class="grid grid-2">
            <div class="carte">
                <h3>Répartition par commission</h3>
                <table>
                    <thead><tr><th>Commission</th><th>Effectif</th></tr></thead>
                    <tbody>
                    <?php foreach ($parCommission as $row): ?>
                        <tr><td><?= e($row['commission']) ?></td><td><?= $row['n'] ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$parCommission): ?><tr><td colspan="2">Aucune donnée.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="carte">
                <h3>Répartition par sous-comité (Anyama)</h3>
                <table>
                    <thead><tr><th>Sous-comité</th><th>Effectif</th></tr></thead>
                    <tbody>
                    <?php foreach ($parAnyama as $row): ?>
                        <tr><td><?= e($row['anyama']) ?></td><td><?= $row['n'] ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$parAnyama): ?><tr><td colspan="2">Aucune donnée.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="carte" style="margin-top:20px;">
            <h3>Répartition par section</h3>
            <table>
                <thead><tr><th>Section</th><th>Effectif</th></tr></thead>
                <tbody>
                <?php foreach ($parSection as $row): ?>
                    <tr><td><?= e($row['section']) ?></td><td><?= $row['n'] ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$parSection): ?><tr><td colspan="2">Aucune donnée.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="carte" style="margin-top:20px;">
            <h3>Répartition par niveau (test d'entrée)</h3>
            <table>
                <thead><tr><th>Niveau</th><th>Effectif</th></tr></thead>
                <tbody>
                <?php foreach ($parNiveau as $row): ?>
                    <tr><td><?= e($row['niveau_affecte']) ?></td><td><?= $row['n'] ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$parNiveau): ?><tr><td colspan="2">Aucun test complété pour le moment.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
