<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);

$dortoirs = $pdo->query("SELECT * FROM dortoirs ORDER BY genre, nom")->fetchAll();
$nbPepiniere = $pdo->query("SELECT COUNT(*) FROM seminaristes WHERE dortoir = 'Pépinière'")->fetchColumn();

$titrePage = "Dortoirs";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre" style="text-align:left;">
            <h2>Occupation des dortoirs</h2>
            <p style="color:var(--texte-doux);">4 dortoirs hommes + 4 dortoirs femmes (répartition équilibrée automatique), plus le dortoir <strong>Pépinière</strong> pour les séminaristes de <?= AGE_PEPINIERE_SEUIL ?> ans et moins.</p>
        </div>

        <div class="carte" style="margin-bottom:20px;display:flex;align-items:center;gap:16px;">
            <div class="stat-card" style="flex:none;"><div class="chiffre"><?= $nbPepiniere ?></div><div class="label">Pépinière</div></div>
            <p style="margin:0;color:var(--texte-doux);">Ce dortoir n'a pas de capacité limitée suivie ici : consultez la <a href="<?= BASE_URL ?>/admin/listes.php">liste nominative par dortoir</a> pour le détail.</p>
        </div>

        <div class="grid grid-2">
            <?php foreach (['Masculin', 'Féminin'] as $genre): ?>
            <div class="carte">
                <h3><?= $genre === 'Masculin' ? '👦 Dortoirs Hommes' : '👧 Dortoirs Femmes' ?></h3>
                <table>
                    <thead><tr><th>Dortoir</th><th>Occupants</th><th>Capacité</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($dortoirs as $d): if ($d['genre'] !== $genre) continue; ?>
                        <?php $taux = $d['capacite'] > 0 ? round($d['occupation'] / $d['capacite'] * 100) : 0; ?>
                        <tr>
                            <td><?= e($d['nom']) ?></td>
                            <td><?= $d['occupation'] ?></td>
                            <td><?= $d['capacite'] ?></td>
                            <td><span class="pill <?= $taux >= 90 ? 'pill-rouge' : ($taux >= 60 ? 'pill-or' : 'pill-vert') ?>"><?= $taux ?>%</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
