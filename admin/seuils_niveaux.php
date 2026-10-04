<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);
$erreurs = []; $succes = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = [];
    foreach (['s1', 's2', 's3'] as $k) { $v[] = (float)str_replace(',', '.', (string)($_POST[$k] ?? '0')); }
    if (!($v[0] > 0 && $v[0] < $v[1] && $v[1] < $v[2] && $v[2] <= 20)) {
        $erreurs[] = "Les notes doivent être croissantes et comprises entre 0 et 20 (ex. 5, 9, 13).";
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
        $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES ('seuils_niveaux', ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")->execute([json_encode($v)]);
        $succes = "Notes enregistrées.";
        $n = 0;
        if (!empty($_POST['recalculer'])) {
            $nouveau = [$v[0], $v[1], $v[2]];
            $st = $pdo->query("SELECT id, note_test, niveau_affecte FROM seminaristes WHERE test_complete = 1 AND note_test IS NOT NULL");
            $up = $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE id = ?");
            foreach ($st->fetchAll() as $s) {
                $note = (float)$s['note_test'];
                $niv = $note < $nouveau[0] ? 'Primaire' : ($note < $nouveau[1] ? 'Secondaire' : ($note < $nouveau[2] ? 'Universitaire' : 'Leader'));
                if ($niv !== $s['niveau_affecte']) { $up->execute([$niv, $s['id']]); $n++; }
            }
            $succes .= " Niveaux recalculés : $n séminariste(s) modifié(s).";
        }
        journaliser($pdo, 'Notes des niveaux modifiées', implode(' / ', $v));
    }
}
$st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'seuils_niveaux'"); $st->execute();
$j = json_decode((string)$st->fetchColumn(), true);
$s = (is_array($j) && count($j) === 3) ? $j : [5, 9, 13];

$titrePage = "Niveaux selon les notes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
$f = function ($x) { return rtrim(rtrim(number_format((float)$x, 2, '.', ''), '0'), '.'); };
?>
<section class="section">
    <div class="container form-wrap">
        <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Niveaux selon les notes</h2></div>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <form method="post" class="carte">
            <p>Le niveau du séminariste dépend de sa note au test d'entrée (sur 20).</p>
            <div class="form-group"><label>🌱 Primaire : de 0 jusqu'à (exclu)</label><input type="number" step="0.25" min="0.25" max="20" name="s1" value="<?= e($f($s[0])) ?>" required></div>
            <div class="form-group"><label>📘 Secondaire : jusqu'à (exclu)</label><input type="number" step="0.25" min="0.25" max="20" name="s2" value="<?= e($f($s[1])) ?>" required></div>
            <div class="form-group"><label>🎓 Universitaire : jusqu'à (exclu)</label><input type="number" step="0.25" min="0.25" max="20" name="s3" value="<?= e($f($s[2])) ?>" required></div>
            <p class="help-text">⭐ Leader : à partir de la dernière note jusqu'à 20.</p>
            <label style="display:flex;gap:8px;align-items:center;margin:12px 0;"><input type="checkbox" name="recalculer" value="1" checked style="width:20px;height:20px;"> Recalculer aussi le niveau des séminaristes qui ont déjà composé le test</label>
            <button class="btn btn-primaire" type="submit">Enregistrer</button>
        </form>
        <div class="carte" style="margin-top:16px;">
            <h3>Résumé actuel</h3>
            <p>Primaire : moins de <?= e($f($s[0])) ?> · Secondaire : <?= e($f($s[0])) ?> à moins de <?= e($f($s[1])) ?> · Universitaire : <?= e($f($s[1])) ?> à moins de <?= e($f($s[2])) ?> · Leader : <?= e($f($s[2])) ?> et plus</p>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
