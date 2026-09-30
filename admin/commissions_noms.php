<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['superadmin']);

$erreurs = [];

function compterMembres(PDO $pdo) {
    return $pdo->query("SELECT commission, COUNT(*) n FROM membres_commission GROUP BY commission")->fetchAll(PDO::FETCH_KEY_PAIR);
}

function preparerParametres(PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur TEXT NOT NULL) ENGINE=InnoDB");
    try { $pdo->exec("ALTER TABLE parametres MODIFY valeur TEXT NOT NULL"); } catch (Throwable $e) { /* déjà TEXT */ }
}

function sauverParam(PDO $pdo, $cle, $valeur) {
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")->execute([$cle, $valeur]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anciens = $_POST['ancien'] ?? [];
    $nouveaux = $_POST['nom'] ?? [];
    $complets = $_POST['complet'] ?? [];
    $supprimer = $_POST['supprimer'] ?? [];
    $ajout = mb_strtoupper(trim($_POST['nouvelle'] ?? ''), 'UTF-8');
    $ajoutComplet = trim($_POST['nouvelle_complet'] ?? '');
    $membres = compterMembres($pdo);

    $liste = []; $renommages = []; $noms = [];
    foreach ($anciens as $i => $ancien) {
        if (isset($supprimer[$i])) {
            if (!empty($membres[$ancien])) { $erreurs[] = "« $ancien » a encore " . (int)$membres[$ancien] . " membre(s) : renommez-la ou déplacez d'abord ses membres."; $liste[] = $ancien; }
            continue;
        }
        $nom = mb_strtoupper(trim($nouveaux[$i] ?? ''), 'UTF-8');
        if ($nom === '') { $erreurs[] = "Le nom de « $ancien » ne peut pas être vide."; $nom = $ancien; }
        if (mb_strlen($nom, 'UTF-8') > 50) { $erreurs[] = "« $nom » dépasse 50 caractères."; $nom = $ancien; }
        if (in_array($nom, $liste, true)) { $erreurs[] = "« $nom » existe déjà."; $nom = $ancien; }
        $liste[] = $nom;
        if ($nom !== $ancien) { $renommages[$ancien] = $nom; }
        $c = trim($complets[$i] ?? '');
        if ($c !== '' && mb_strtoupper($c, 'UTF-8') !== $nom) { $noms[] = $nom . ' = ' . $c; }
    }
    if ($ajout !== '') {
        if (mb_strlen($ajout, 'UTF-8') > 50) { $erreurs[] = "Le nouveau nom dépasse 50 caractères."; }
        elseif (in_array($ajout, $liste, true)) { $erreurs[] = "« $ajout » existe déjà."; }
        else {
            $liste[] = $ajout;
            if ($ajoutComplet !== '') { $noms[] = $ajout . ' = ' . $ajoutComplet; }
        }
    }
    if (!$liste) { $erreurs[] = "Il faut au moins une commission."; }

    if (!$erreurs) {
        preparerParametres($pdo);
        $pdo->beginTransaction();
        try {
            // éviter les collisions lors d'échanges de noms : passage par un nom temporaire
            foreach ($renommages as $ancien => $nouveau) {
                $pdo->prepare("UPDATE membres_commission SET commission = ? WHERE commission = ?")->execute(['~tmp~' . $ancien, $ancien]);
                $pdo->prepare("UPDATE critiques SET commission = ? WHERE commission = ?")->execute(['~tmp~' . $ancien, $ancien]);
            }
            foreach ($renommages as $ancien => $nouveau) {
                $pdo->prepare("UPDATE membres_commission SET commission = ? WHERE commission = ?")->execute([$nouveau, '~tmp~' . $ancien]);
                $pdo->prepare("UPDATE critiques SET commission = ? WHERE commission = ?")->execute([$nouveau, '~tmp~' . $ancien]);
            }
            sauverParam($pdo, 'liste_commissions', implode("\n", $liste));
            sauverParam($pdo, 'noms_commissions', implode("\n", $noms));
            $pdo->commit();
            $_SESSION['flash_succes'] = "Commissions enregistrées" . ($renommages ? " (" . count($renommages) . " renommée(s), les membres et leurs badges sont à jour)." : ".");
            redirect('/admin/commissions_noms');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            $erreurs[] = "Enregistrement impossible : " . $e->getMessage();
        }
    }
}

$membres = compterMembres($pdo);
$liste = listeCommissions();
foreach (array_keys($membres) as $c) { if (!in_array($c, $liste, true)) { $liste[] = $c; } }
$map = nomsCommissionsComplets();

$titrePage = "Noms des commissions";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container" style="max-width:900px;">
        <div class="section-titre"><span class="eyebrow">Super administrateur</span><h2>Noms des commissions</h2>
            <p>Le <strong>nom</strong> est celui choisi dans les formulaires. Le <strong>nom complet</strong> (facultatif) apparaît sur les badges et les certificats.</p></div>
        <?php if (!empty($_SESSION['flash_succes'])): ?><div class="alert alert-succes"><?= e($_SESSION['flash_succes']) ?></div><?php unset($_SESSION['flash_succes']); endif; ?>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>

        <form method="post" class="carte" style="overflow-x:auto;">
            <table>
                <thead><tr><th>Nom</th><th>Nom complet (badge et certificat)</th><th>Membres</th><th>Supprimer</th></tr></thead>
                <tbody>
                <?php foreach ($liste as $i => $c): $long = $map[mb_strtoupper($c, 'UTF-8')] ?? ''; ?>
                    <tr>
                        <td><input type="hidden" name="ancien[<?= $i ?>]" value="<?= e($c) ?>"><input type="text" name="nom[<?= $i ?>]" value="<?= e($c) ?>" maxlength="50" required></td>
                        <td><input type="text" name="complet[<?= $i ?>]" value="<?= e($long) ?>" maxlength="120" placeholder="Identique au nom"></td>
                        <td><?= (int)($membres[$c] ?? 0) ?></td>
                        <td><?php if (empty($membres[$c])): ?><label style="margin:0;"><input type="checkbox" name="supprimer[<?= $i ?>]" value="1"> Supprimer</label><?php else: ?><small style="color:var(--texte-doux)">a des membres</small><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                    <tr>
                        <td><input type="text" name="nouvelle" maxlength="50" placeholder="+ Nouvelle commission"></td>
                        <td><input type="text" name="nouvelle_complet" maxlength="120" placeholder="Nom complet (facultatif)"></td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primaire" style="margin-top:16px;">Enregistrer</button>
        </form>
        <p class="help-text" style="margin-top:12px;">Renommer une commission met à jour tous ses membres ; leurs badges et certificats affichent aussitôt le nouveau nom. Une commission qui a des membres ne peut pas être supprimée.</p>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
