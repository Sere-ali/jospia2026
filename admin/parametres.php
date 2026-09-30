<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);

$erreurs = [];
$defs = [
    'frais_participation' => ['Frais de participation (FCFA)', 'number', (string)FRAIS_PARTICIPATION],
    'wave_numero'         => ['Numéro Wave qui reçoit les paiements (sans 225)', 'text', WAVE_NUMERO],
    'wave_payment_link'   => ['Lien de paiement Wave marchand', 'text', WAVE_PAYMENT_LINK],
    'age_pepiniere'       => ['Âge maximum du dortoir Pépinière (ans)', 'number', (string)AGE_PEPINIERE_SEUIL],
    'duree_test_minutes'  => ["Durée du test d'entrée (minutes)", 'number', (string)DUREE_TEST_MINUTES],
];
$bascules = [
    'inscriptions_seminaristes' => ['Inscriptions des séminaristes ouvertes', '1'],
    'inscriptions_commission'   => ['Inscriptions des membres de commission ouvertes', '1'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = [];
    foreach ($defs as $cle => [$lib, $type, $def]) { $v[$cle] = trim($_POST[$cle] ?? ''); }
    if (!ctype_digit($v['frais_participation']) || (int)$v['frais_participation'] < 100) $erreurs[] = "Frais de participation invalides.";
    if (!preg_match('/^\d{8,15}$/', preg_replace('/\D+/', '', $v['wave_numero']))) $erreurs[] = "Numéro Wave invalide.";
    if ($v['wave_payment_link'] !== '' && !preg_match('#^https://#i', $v['wave_payment_link'])) $erreurs[] = "Le lien Wave doit commencer par https://";
    if (!ctype_digit($v['age_pepiniere']) || (int)$v['age_pepiniere'] > 18) $erreurs[] = "Âge Pépinière invalide (0 à 18).";
    if (!ctype_digit($v['duree_test_minutes']) || (int)$v['duree_test_minutes'] < 1 || (int)$v['duree_test_minutes'] > 240) $erreurs[] = "Durée du test invalide (1 à 240 minutes).";
    if (!$erreurs) {
        $v['wave_numero'] = numeroLocal($v['wave_numero']);
        foreach ($bascules as $cle => $b) { $v[$cle] = isset($_POST[$cle]) ? '1' : '0'; }
        $v['noms_commissions'] = trim(str_replace("\r", '', $_POST['noms_commissions'] ?? ''));
        $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur TEXT NOT NULL) ENGINE=InnoDB");
        try { $pdo->exec("ALTER TABLE parametres MODIFY valeur TEXT NOT NULL"); } catch (Throwable $e) { /* déjà TEXT */ }
        $st = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
        foreach ($v as $cle => $val) { $st->execute([$cle, $val]); }
        $_SESSION['flash_succes'] = "Paramètres enregistrés.";
        redirect('/admin/parametres');
    }
}

$titrePage = "Paramètres du site";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:760px;">
        <div class="section-titre"><span class="eyebrow">Super administrateur</span><h2>Paramètres du site</h2></div>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>
        <form method="post" class="carte">
            <?php foreach ($defs as $cle => [$lib, $type, $def]): ?>
                <div class="form-group">
                    <label><?= e($lib) ?></label>
                    <input type="<?= $type === 'number' ? 'number' : 'text' ?>" name="<?= $cle ?>" value="<?= e($_POST[$cle] ?? $def) ?>" <?= $type === 'number' ? 'min="0"' : '' ?> required>
                </div>
            <?php endforeach; ?>
            <h3 style="margin-top:20px;">Inscriptions</h3>
            <?php foreach ($bascules as $cle => [$lib, $def]): ?>
                <label class="option-item" style="display:block;margin-bottom:8px;"><input type="checkbox" name="<?= $cle ?>" value="1" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST[$cle]) : parametre($cle, $def) === '1') ? 'checked' : '' ?>> <?= e($lib) ?></label>
            <?php endforeach; ?>
            <h3 style="margin-top:20px;">Noms complets des commissions</h3>
            <p class="help-text">Affichés sur les badges et les certificats. Une ligne par commission : <code>SIGLE = Nom complet</code>. Exemple : <code>MIC = Mobilisation, Information et Communication</code></p>
            <?php
            $noms = $_POST['noms_commissions'] ?? null;
            if ($noms === null) {
                $noms = [];
                foreach (listeCommissions() as $c) { $noms[] = $c . ' = ' . nomCommissionComplet($c); }
                $noms = implode("\n", $noms);
            }
            ?>
            <textarea name="noms_commissions" rows="14" style="font-family:var(--police-mono);"><?= e($noms) ?></textarea>
            <button type="submit" class="btn btn-primaire" style="margin-top:14px;">Enregistrer les paramètres</button>
        </form>
        <p style="color:var(--texte-doux);margin-top:12px;">Les changements s'appliquent immédiatement à tout le site (page de paiement, inscriptions, test d'entrée...).</p>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
