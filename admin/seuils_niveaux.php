<?php
require_once __DIR__ . '/../includes/init.php';
$PARTAGE = !empty($PARTAGE); // vrai quand la page est ouverte par le lien secret (sans compte)
if (!$PARTAGE) exigerRole(['scientifique', 'superadmin']);
$pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
$erreurs = []; $succes = null;
$reg = reglageNiveaux();
$anciensNoms = $reg['noms'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noms = []; $anc = []; $fins = [];
    foreach ((array)($_POST['nom'] ?? []) as $i => $n) {
        $noms[] = trim(preg_replace('/\s+/', ' ', (string)$n));
        $anc[] = (string)($_POST['ancien'][$i] ?? '');
        $fins[] = (float)str_replace(',', '.', (string)($_POST['fin'][$i] ?? '0'));
    }
    $nb = count($noms);
    $v = array_slice($fins, 0, max(0, $nb - 1)); // la dernière tranche va jusqu'à 20
    $minuscules = array_map(function ($n) { return mb_strtolower($n, 'UTF-8'); }, $noms);
    $croissant = $nb >= 2 && $v[0] > 0 && end($v) <= 20;
    for ($i = 1; $i < count($v); $i++) { if (!($v[$i - 1] < $v[$i])) $croissant = false; }
    if ($nb < 2 || $nb > 12) {
        $erreurs[] = "Il faut entre 2 et 12 niveaux.";
    } elseif (in_array('', $noms, true) || count(array_unique($minuscules)) < $nb || in_array('pépinière', $minuscules, true) || in_array('test non composé', $minuscules, true) || max(array_map('mb_strlen', $noms)) > 30) {
        $erreurs[] = "Les noms de niveaux sont obligatoires, différents les uns des autres (30 caractères maximum).";
    } elseif (!$croissant) {
        $erreurs[] = "Les intervalles doivent être croissants et compris entre 0 et 20 (ex. 5, 9, 13).";
    } else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
        $ins = $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES (?, ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)");
        $ins->execute(['seuils_niveaux', json_encode($v)]);
        $ins->execute(['noms_niveaux', json_encode($noms, JSON_UNESCAPED_UNICODE)]);
        // Renommage : les séminaristes déjà classés suivent le nouveau nom (nom temporaire : évite les collisions)
        $renom = [];
        foreach ($noms as $i => $n) { if ($anc[$i] !== '' && in_array($anc[$i], $anciensNoms, true) && $anc[$i] !== $n) $renom[$i] = $anc[$i]; }
        foreach ($renom as $i => $ancien) { $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE niveau_affecte = ?")->execute(["\x01" . $i, $ancien]); }
        foreach ($renom as $i => $ancien) { $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE niveau_affecte = ?")->execute([$noms[$i], "\x01" . $i]); }
        $succes = "Niveaux enregistrés.";
        // Recalcul : tous si demandé, sinon uniquement les séminaristes dont le niveau a été supprimé
        $n = 0;
        $up = $pdo->prepare("UPDATE seminaristes SET niveau_affecte = ? WHERE id = ?");
        foreach ($pdo->query("SELECT id, note_test, niveau_affecte FROM seminaristes WHERE test_complete = 1 AND note_test IS NOT NULL")->fetchAll() as $s) {
            $orphelin = !in_array($s['niveau_affecte'], $noms, true) && $s['niveau_affecte'] !== 'Pépinière';
            if (empty($_POST['recalculer']) && !$orphelin) continue;
            $niv = niveauPourNote((float)$s['note_test'], $noms, $v);
            if ($niv !== $s['niveau_affecte']) { $up->execute([$niv, $s['id']]); $n++; }
        }
        if ($n) $succes .= " Niveaux recalculés : $n séminariste(s) modifié(s).";
        if (!$PARTAGE) journaliser($pdo, 'Niveaux modifiés', implode(' / ', $noms) . ' : ' . implode(' / ', $v));
        reglageNiveaux(true);
    }
}
if ($erreurs && isset($_POST['nom'])) {
    $lignes = [];
    foreach ((array)$_POST['nom'] as $i => $n) { $lignes[] = ['nom' => (string)$n, 'ancien' => (string)($_POST['ancien'][$i] ?? ''), 'fin' => (string)($_POST['fin'][$i] ?? '')]; }
} else {
    $st = $pdo->prepare("SELECT valeur FROM parametres WHERE cle = 'seuils_niveaux'"); $st->execute();
    $reg = ['noms' => nomsNiveaux(), 'seuils' => seuilsNiveaux()];
    $lignes = [];
    foreach ($reg['noms'] as $i => $n) { $lignes[] = ['nom' => $n, 'ancien' => $n, 'fin' => isset($reg['seuils'][$i]) ? rtrim(rtrim(number_format($reg['seuils'][$i], 2, '.', ''), '0'), '.') : '20']; }
}

$titrePage = "Niveaux selon les notes";
require_once __DIR__ . '/../includes/header.php';
if (!$PARTAGE) require_once __DIR__ . '/../includes/admin_nav.php';
?>
<section class="section">
    <div class="container">
        <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Niveaux selon les notes</h2></div>
        <?php foreach ($erreurs as $er): ?><div class="alert alert-erreur"><?= e($er) ?></div><?php endforeach; ?>
        <?php if ($succes): ?><div class="alert alert-succes"><?= e($succes) ?></div><?php endif; ?>
        <form method="post" id="form-niveaux" data-no-ajax>
            <p style="margin-bottom:14px;">Le niveau du séminariste dépend de sa note au test d'entrée (sur 20). Modifiez le <strong>nom</strong> et l'<strong>intervalle</strong> de chaque niveau, ou ajoutez-en un.</p>
            <div class="grid grid-2" id="liste-niveaux"></div>
            <p style="margin:14px 0;"><button type="button" class="btn btn-or" id="btn-ajouter">➕ Ajouter un niveau</button></p>
            <label style="display:flex;gap:8px;align-items:center;margin:16px 0;"><input type="checkbox" name="recalculer" value="1" checked style="width:20px;height:20px;"> Recalculer aussi le niveau des séminaristes qui ont déjà composé le test</label>
            <button class="btn btn-primaire" type="submit">Enregistrer</button>
        </form>
    </div>
</section>
<script>
(function () {
    var donnees = <?= json_encode($lignes, JSON_UNESCAPED_UNICODE) ?>;
    var liste = document.getElementById('liste-niveaux'), f = document.getElementById('form-niveaux');
    function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;'); }
    function carte(d, i, n) {
        var dernier = i === n - 1;
        return '<div class="carte niv">' +
            '<label style="font-weight:700;">Niveau ' + (i + 1) + ' : nom</label>' +
            '<input type="text" name="nom[]" maxlength="30" required style="font-weight:700;font-size:1.1rem;" value="' + esc(d.nom) + '">' +
            '<input type="hidden" name="ancien[]" value="' + esc(d.ancien) + '">' +
            '<label style="margin-top:12px;display:block;">Intervalle de notes (sur 20)</label>' +
            '<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;"><span>De</span>' +
            '<input type="number" class="debut" disabled style="width:90px;"><span>à</span>' +
            (dernier ? '<input type="number" value="20" disabled style="width:90px;"><input type="hidden" name="fin[]" value="20">'
                     : '<input type="number" name="fin[]" step="0.25" min="0.25" max="20" required class="fin" style="width:90px;" value="' + esc(d.fin) + '">') +
            '<small>(fin exclue)</small></div>' +
            (n > 2 ? '<p style="margin:10px 0 0;"><button type="button" class="btn btn-sm btn-danger sup">🗑️ Supprimer ce niveau</button></p>' : '') + '</div>';
    }
    function dessiner() {
        liste.innerHTML = donnees.map(function (d, i) { return carte(d, i, donnees.length); }).join('');
        majDebuts();
    }
    function lire() {
        var cartes = liste.querySelectorAll('.niv');
        donnees = [].map.call(cartes, function (c) {
            var fin = c.querySelector('input.fin');
            return { nom: c.querySelector('[name="nom[]"]').value, ancien: c.querySelector('[name="ancien[]"]').value, fin: fin ? fin.value : '20' };
        });
    }
    function majDebuts() {
        var cartes = liste.querySelectorAll('.niv'), prec = '0';
        [].forEach.call(cartes, function (c) {
            c.querySelector('.debut').value = prec;
            var fin = c.querySelector('input.fin'); if (fin) prec = fin.value;
        });
    }
    liste.addEventListener('input', majDebuts);
    liste.addEventListener('click', function (e) {
        var b = e.target.closest('.sup'); if (!b) return;
        lire(); var i = [].indexOf.call(liste.children, b.closest('.niv')); donnees.splice(i, 1);
        if (i === donnees.length) donnees[donnees.length - 1].fin = '20'; // le nouveau dernier niveau va jusqu'à 20
        dessiner();
    });
    document.getElementById('btn-ajouter').addEventListener('click', function () {
        lire(); if (donnees.length >= 12) return;
        var n = donnees.length, prev = parseFloat(donnees[n - 2] ? donnees[n - 2].fin : 0) || 0;
        var av = parseFloat(donnees[n - 1].fin);
        // l'ancien dernier niveau reçoit une borne intermédiaire, le nouveau va jusqu'à 20
        var borne = Math.min(19, Math.max(prev + 1, Math.round((prev + 20) / 2)));
        donnees[n - 1].fin = String(borne);
        donnees.push({ nom: '', ancien: '', fin: '20' });
        dessiner();
        liste.lastElementChild.querySelector('[name="nom[]"]').focus();
    });
    dessiner();
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
