<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);
$erreurs = []; $succes = null;
$anciensNoms = nomsNiveaux();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noms = [];
    foreach ([0, 1, 2, 3] as $i) { $noms[] = trim(preg_replace('/\s+/', ' ', (string)($_POST['nom' . $i] ?? ''))); }
    $v = [];
    foreach (['s1', 's2', 's3'] as $k) { $v[] = (float)str_replace(',', '.', (string)($_POST[$k] ?? '0')); }
    $minuscules = array_map(function ($n) { return mb_strtolower($n, 'UTF-8'); }, $noms);
    if (in_array('', $noms, true) || count(array_unique($minuscules)) < 4 || in_array('pépinière', $minuscules, true) || in_array('test non composé', $minuscules, true) || max(array_map('mb_strlen', $noms)) > 30) {
        $erreurs[] = "Les 4 noms de niveaux sont obligatoires, différents les uns des autres (30 caractères maximum).";
    } elseif (!($v[0] > 0 && $v[0] < $v[1] && $v[1] < $v[2] && $v[2] <= 20)) {
        $erreurs[] = "Les intervalles doivent être croissants et compris entre 0 et 20 (ex. 5, 9, 13).";
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
        $ins = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
        $ins->execute(['seuils_niveaux', json_encode($v)]);
        $ins->execute(['noms_niveaux', json_encode($noms, JSON_UNESCAPED_UNICODE)]);
        // Renommage : les séminaristes déjà classés suivent le nouveau nom (passage par un nom temporaire : évite les collisions)
        $ren = 0;
        foreach ($anciensNoms as $i => $ancien) {
            if ($ancien !== $noms[$i]) { $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE niveau_affecte = ?")->execute(["\x01" . $i, $ancien]); $ren++; }
        }
        foreach ($anciensNoms as $i => $ancien) {
            if ($ancien !== $noms[$i]) { $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE niveau_affecte = ?")->execute([$noms[$i], "\x01" . $i]); }
        }
        $succes = "Niveaux enregistrés.";
        if (!empty($_POST['recalculer'])) {
            $n = 0;
            $up = $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE id = ?");
            foreach ($pdo->query("SELECT id, note_test, niveau_affecte FROM seminaristes WHERE test_complete = 1 AND note_test IS NOT NULL")->fetchAll() as $s) {
                $note = (float)$s['note_test'];
                $niv = $note < $v[0] ? $noms[0] : ($note < $v[1] ? $noms[1] : ($note < $v[2] ? $noms[2] : $noms[3]));
                if ($niv !== $s['niveau_affecte']) { $up->execute([$niv, $s['id']]); $n++; }
            }
            $succes .= " Niveaux recalculés : $n séminariste(s) modifié(s).";
        }
        journaliser($pdo, 'Niveaux modifiés', implode(' / ', $noms) . ' : ' . implode(' / ', $v));
    }
}
$st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'seuils_niveaux'"); $st->execute();
$j = json_decode((string)$st->fetchColumn(), true);
$s = (is_array($j) && count($j) === 3) ? $j : [5, 9, 13];
$noms = $erreurs && isset($_POST['nom0']) ? array_map(function ($i) { return (string)($_POST['nom' . $i] ?? ''); }, [0, 1, 2, 3]) : nomsNiveaux();
$f = function ($x) { return rtrim(rtrim(number_format((float)$x, 2, '.', ''), '0'), '.'); };
$icones = ['🌱', '📘', '🎓', '⭐'];

$titrePage = "Niveaux selon les notes";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Niveaux selon les notes</h2></div>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <form method="post" id="form-niveaux">
            <p style="margin-bottom:14px;">Le niveau du séminariste dépend de sa note au test d'entrée (sur 20). Vous pouvez modifier le <strong>nom</strong> de chaque niveau et son <strong>intervalle</strong>.</p>
            <div class="grid grid-2">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="carte">
                    <label style="font-weight:700;"><?= $icones[$i] ?> Niveau <?= $i + 1 ?> : nom</label>
                    <input type="text" name="nom<?= $i ?>" maxlength="30" value="<?= e($noms[$i]) ?>" required style="font-weight:700;font-size:1.1rem;">
                    <label style="margin-top:12px;display:block;">Intervalle de notes (sur 20)</label>
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <span>De</span>
                        <?php if ($i === 0): ?><input type="number" value="0" disabled style="width:90px;">
                        <?php else: ?><input type="number" step="0.25" min="0.25" max="20" data-min="<?= $i ?>" value="<?= e($f($s[$i - 1])) ?>" style="width:90px;" tabindex="-1" readonly title="Égal à la fin du niveau précédent"><?php endif; ?>
                        <span>à</span>
                        <?php if ($i < 3): ?><input type="number" step="0.25" min="0.25" max="20" name="s<?= $i + 1 ?>" value="<?= e($f($s[$i])) ?>" style="width:90px;" required>
                        <?php else: ?><input type="number" value="20" disabled style="width:90px;"><?php endif; ?>
                        <small>(fin exclue<?= $i === 3 ? ' : jusqu\'à 20' : '' ?>)</small>
                    </div>
                </div>
            <?php endfor; ?>
            </div>
            <label style="display:flex;gap:8px;align-items:center;margin:16px 0;"><input type="checkbox" name="recalculer" value="1" checked style="width:20px;height:20px;"> Recalculer aussi le niveau des séminaristes qui ont déjà composé le test</label>
            <button class="btn btn-primaire" type="submit">Enregistrer</button>
        </form>
    </div>
</section>
<script>
(function () {
    var f = document.getElementById('form-niveaux'); if (!f) return;
    ['s1', 's2', 's3'].forEach(function (n, i) {
        var champ = f.querySelector('[name=' + n + ']'), suivant = f.querySelector('[data-min="' + (i + 1) + '"]');
        if (champ && suivant) champ.addEventListener('input', function () { suivant.value = champ.value; });
    });
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
