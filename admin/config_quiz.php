<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);

$titrePage = "Configuration du Quiz";

$succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['configurer'])) {
    $stmt = $pdo->prepare("UPDATE config_quiz SET nb_questions_a_tirer = ? WHERE banque = ?");
    
    foreach ($_POST['nb_questions'] as $banque => $nb) {
        $stmt->execute([(int)$nb, (int)$banque]);
    }
    
    $succes = "Configuration enregistrée avec succès.";
}

$banques = $pdo->query("SELECT * FROM config_quiz ORDER BY banque")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>

<section class="section">
    <div class="container" style="max-width: 800px;">
        <div class="section-titre">
            <h2>Configuration des banques de questions</h2>
            <p>Définissez combien de questions doivent être tirées aléatoirement dans chaque banque pour composer le test d'un séminariste.</p>
        </div>
        
        <p style="margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/admin/questions" class="btn btn-sm btn-outline">← Retour aux questions</a>
        </p>

        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>

        <div class="carte">
            <form method="post">
                <input type="hidden" name="configurer" value="1">
                
                <table style="margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 15%;">N° Banque</th>
                            <th>Nom de la banque</th>
                            <th style="width: 30%;">Nombre à tirer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        foreach ($banques as $b): 
                            $total += $b['nb_questions_a_tirer'];
                        ?>
                        <tr>
                            <td class="text-center"><strong><?= $b['banque'] ?></strong></td>
                            <td><?= e($b['nom_banque']) ?></td>
                            <td>
                                <input type="number" name="nb_questions[<?= $b['banque'] ?>]" value="<?= $b['nb_questions_a_tirer'] ?>" min="0" max="50" style="width: 100%; padding: 8px;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="2" class="text-right"><strong>TOTAL DE QUESTIONS DU TEST :</strong></td>
                            <td class="text-center"><strong style="font-size: 1.2rem; color: var(--primaire);"><?= $total ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="help-text" style="margin-bottom: 20px;">
                    💡 <strong>Note sur la correction :</strong> Quelle que soit la somme totale configurée (ex: 20, 25, 30 questions), 
                    la note finale du séminariste sera automatiquement calculée de façon proportionnelle pour toujours être ramenée 
                    sur <strong>20 points</strong>. Les seuils de niveaux (Primaire, Secondaire, Universitaire, Leader) restent donc valides.
                </div>
                
                <button type="submit" class="btn btn-primaire btn-block">Enregistrer la configuration</button>
            </form>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
