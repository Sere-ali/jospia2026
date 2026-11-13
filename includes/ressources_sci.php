<?php
/** Archives de la commission scientifique : fichiers du dépôt (conservés tels quels) + fichiers ajoutés depuis le site (stockés en base, car le disque de Render est effacé à chaque mise à jour). */
const RES_MAX_OCTETS = 15 * 1024 * 1024;
const RES_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'txt', 'csv', 'png', 'jpg', 'jpeg', 'zip'];
function resRacine() { $r = realpath(__DIR__ . '/../ressources_scientifique'); return $r !== false ? $r : __DIR__ . '/../ressources_scientifique'; }
function resResoudre($rel) {
    $rel = trim(str_replace('\\', '/', (string)$rel), '/');
    if ($rel === '') return resRacine();
    if (strpos($rel, "\0") !== false) return null;
    foreach (explode('/', $rel) as $p) { if ($p === '' || $p === '.' || $p === '..' || $p[0] === '.') return null; }
    $p = realpath(resRacine() . '/' . $rel);
    if ($p === false || strpos($p, resRacine() . DIRECTORY_SEPARATOR) !== 0) return null;
    return $p;
}
function resRelatif($chemin) { return ltrim(substr($chemin, strlen(resRacine())), '/'); }
function resTaille($n) {
    if ($n >= 1048576) return number_format($n / 1048576, 1, ',', ' ') . ' Mo';
    return max(1, (int)round($n / 1024)) . ' Ko';
}
function resIcone($nom) {
    $x = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
    return ['pdf' => '📕', 'doc' => '📘', 'docx' => '📘', 'xls' => '📗', 'xlsx' => '📗', 'ppt' => '📙', 'pptx' => '📙', 'odp' => '📙', 'png' => '🖼️', 'jpg' => '🖼️', 'jpeg' => '🖼️'][$x] ?? '📄';
}

function resTable() {
    static $ok = false; global $pdo;
    if ($ok) return;
    $pdo->exec("CREATE TABLE IF NOT EXISTS archives_fichiers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dossier VARCHAR(400) NOT NULL DEFAULT '',
        nom VARCHAR(255) NOT NULL,
        taille INT NOT NULL DEFAULT 0,
        ajoute_par VARCHAR(120) DEFAULT NULL,
        donnees LONGBLOB NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_dossier_nom (dossier(190), nom(190))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $ok = true;
}
/** Fichier demandé : sur le disque (dépôt) ou ajouté depuis le site (base). Retourne null s'il n'existe pas. */
function resLire($rel) {
    global $pdo;
    $f = resResoudre($rel);
    if ($f && is_file($f)) return ['nom' => basename($f), 'taille' => filesize($f), 'chemin' => $f];
    $rel = trim(str_replace('\\', '/', (string)$rel), '/');
    $dossier = strpos($rel, '/') === false ? '' : dirname($rel);
    $nom = basename($rel);
    if ($nom === '' || $nom[0] === '.') return null;
    try {
        resTable();
        $st = $pdo->prepare("SELECT nom, taille, donnees FROM archives_fichiers WHERE dossier = ? AND nom = ?");
        $st->execute([$dossier, $nom]);
        $r = $st->fetch();
    } catch (Throwable $e) { $r = false; }
    return $r ? ['nom' => $r['nom'], 'taille' => (int)$r['taille'], 'donnees' => $r['donnees']] : null;
}
function resNomSur($nom) {
    $nom = basename(str_replace('\\', '/', (string)$nom));
    $nom = preg_replace('/[\x00-\x1F\x7F"*:<>?|\/\\\\]+/u', ' ', $nom);
    $nom = trim(preg_replace('/\s+/u', ' ', $nom), " .");
    if (mb_strlen($nom) > 200) { $x = pathinfo($nom, PATHINFO_EXTENSION); $nom = mb_substr(pathinfo($nom, PATHINFO_FILENAME), 0, 190) . ($x ? '.' . $x : ''); }
    return $nom;
}
function resJeton() {
    if (empty($_SESSION['res_jeton'])) $_SESSION['res_jeton'] = bin2hex(random_bytes(16));
    return $_SESSION['res_jeton'];
}
/** Ajout / suppression de fichiers (formulaires de la page). Redirige ensuite vers la page. */
function resTraiterActions($urlBase, $peutSupprimer) {
    global $pdo;
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['res_action'])) return;
    $dossier = resResoudre($_POST['r'] ?? '');
    $rel = ($dossier && is_dir($dossier)) ? resRelatif($dossier) : '';
    $retour = function ($ok, $msg) use ($urlBase, $rel) {
        $_SESSION['res_flash'] = [$ok, $msg];
        header('Location: ' . $urlBase . ($rel !== '' ? '?r=' . rawurlencode($rel) : ''));
        exit;
    };
    if (!hash_equals(resJeton(), (string)($_POST['jeton'] ?? ''))) $retour(false, "Session expirée : rechargez la page puis recommencez.");
    resTable();
    if ($_POST['res_action'] === 'supprimer' && $peutSupprimer) {
        $pdo->prepare("DELETE FROM archives_fichiers WHERE id = ? AND dossier = ?")->execute([(int)($_POST['id'] ?? 0), $rel]);
        $retour(true, "Fichier supprimé.");
    }
    if ($_POST['res_action'] !== 'ajouter') $retour(false, "Action inconnue.");
    $u = utilisateurCourant();
    $auteur = $u ? ($u['nom_affiche'] ?? $u['identifiant'] ?? 'Compte') : 'Lien partagé';
    $f = $_FILES['fichiers'] ?? null;
    if (!$f || empty($f['name'])) $retour(false, "Aucun fichier reçu (taille maximale : 15 Mo par fichier).");
    $paquet = 0; try { $paquet = (int)$pdo->query("SELECT @@max_allowed_packet")->fetchColumn(); } catch (Throwable $e) {}
    $ok = 0; $erreurs = [];
    foreach ((array)$f['name'] as $i => $nomOrig) {
        $nom = resNomSur($nomOrig);
        if ($nom === '') continue;
        $err = $f['error'][$i];
        if ($err === UPLOAD_ERR_NO_FILE) continue;
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) { $erreurs[] = "$nom : trop volumineux (15 Mo maximum)"; continue; }
        if ($err !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'][$i])) { $erreurs[] = "$nom : échec de l'envoi"; continue; }
        $x = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
        if (!in_array($x, RES_EXTENSIONS, true)) { $erreurs[] = "$nom : type de fichier non accepté"; continue; }
        $taille = (int)$f['size'][$i];
        if ($taille > RES_MAX_OCTETS || ($paquet && $taille + 4096 > $paquet)) { $erreurs[] = "$nom : trop volumineux (15 Mo maximum)"; continue; }
        // Nom déjà pris (dépôt ou base) : on numérote
        $base = pathinfo($nom, PATHINFO_FILENAME); $final = $nom; $n = 2;
        while (resLire(($rel === '' ? '' : $rel . '/') . $final) !== null) { $final = $base . ' (' . $n++ . ')' . ($x ? '.' . $x : ''); }
        try {
            $pdo->prepare("INSERT INTO archives_fichiers (dossier, nom, taille, ajoute_par, donnees) VALUES (?,?,?,?,?)")
                ->execute([$rel, $final, $taille, mb_substr($auteur, 0, 120), file_get_contents($f['tmp_name'][$i])]);
            $ok++;
        } catch (Throwable $e) { error_log('Archives : ' . $e->getMessage()); $erreurs[] = "$nom : enregistrement impossible"; }
    }
    $msg = $ok ? ($ok . ' fichier(s) ajouté(s).') : '';
    if ($erreurs) $msg .= ($msg ? ' ' : '') . implode(' ; ', $erreurs) . '.';
    $retour($ok > 0 && !$erreurs, $msg ?: "Aucun fichier ajouté.");
}

/** Lien public signé d'un fichier : sert à ouvrir directement dans Word / Excel / PowerPoint (qui ne peuvent pas se connecter au site). */
function resSignature($rel) { return substr(hash_hmac('sha256', (string)$rel, 'archives|' . DB_PASS . '|' . DB_NAME . '|' . DB_HOST), 0, 24); }
function resUrlPublique($rel) {
    return urlSite() . '/archives/' . resSignature($rel) . '/' . implode('/', array_map('rawurlencode', explode('/', $rel)));
}
/** Application Office qui ouvre le fichier pour le modifier (null = pas un document Office). */
function resAppOffice($nom) {
    $x = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
    $m = ['doc' => 'word', 'docx' => 'word', 'docm' => 'word', 'xls' => 'excel', 'xlsx' => 'excel', 'csv' => 'excel', 'ppt' => 'powerpoint', 'pptx' => 'powerpoint', 'odp' => 'powerpoint'];
    return $m[$x] ?? null;
}
/** Envoie le fichier tel quel (jamais interprété par le navigateur). $mode 'inline' : PDF, images et texte s'ouvrent à l'écran. */
function resTelecharger($rel, $mode = 'download') {
    $fic = resLire($rel);
    if (!$fic) { http_response_code(404); die('Fichier introuvable.'); }
    $nom = $fic['nom'];
    $x = strtolower(pathinfo($nom, PATHINFO_EXTENSION));
    $types = ['docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'pdf' => 'application/pdf', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'odp' => 'application/vnd.oasis.opendocument.presentation'];
    $type = $types[$x] ?? 'application/octet-stream';
    $inline = false;
    if ($mode === 'inline') {
        if (in_array($x, ['pdf', 'png', 'jpg', 'jpeg'], true)) { $inline = true; }
        elseif (in_array($x, ['html', 'htm', 'mht', 'txt'], true)) { $inline = true; $type = 'text/plain; charset=utf-8'; }
    }
    while (ob_get_level()) ob_end_clean();
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: sandbox");
    header('Content-Type: ' . $type);
    header('Content-Length: ' . $fic['taille']);
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename=\"" . str_replace(['"', '\\', "\r", "\n"], '_', $nom) . "\"; filename*=UTF-8''" . rawurlencode($nom));
    header('Cache-Control: private, max-age=600');
    if (isset($fic['chemin'])) { readfile($fic['chemin']); } else { echo $fic['donnees']; }
    exit;
}
/** Affiche le dossier demandé (?r=) ; $urlBase = adresse de la page (sans paramètres). */
function resAfficher($urlBase, $peutSupprimer = false) {
    global $pdo;
    $rel = (string)($_GET['r'] ?? '');
    $dossier = resResoudre($rel);
    if (!$dossier || !is_dir($dossier)) { $rel = ''; $dossier = resRacine(); }
    $rel = resRelatif($dossier);
    $dossiers = []; $fichiers = [];
    foreach (scandir($dossier) as $n) {
        if ($n[0] === '.') continue;
        $p = $dossier . '/' . $n;
        if (is_dir($p)) { $dossiers[] = $n; } elseif (is_file($p)) { $fichiers[] = $n; }
    }
    natcasesort($dossiers);
    // Fichiers ajoutés depuis le site (base)
    $ajoutes = [];
    try {
        resTable();
        $st = $pdo->prepare("SELECT id, nom, taille, ajoute_par, created_at FROM archives_fichiers WHERE dossier = ?");
        $st->execute([$rel]);
        foreach ($st->fetchAll() as $r) { $ajoutes[$r['nom']] = $r; $fichiers[] = $r['nom']; }
    } catch (Throwable $e) {}
    natcasesort($fichiers);
    $flash = $_SESSION['res_flash'] ?? null; unset($_SESSION['res_flash']);
    $lien = function ($cle, $val) use ($urlBase) { return e($urlBase . '?' . $cle . '=' . rawurlencode($val)); };
    ?>
<section class="section"><div class="container" style="max-width:900px;">
    <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Archives</h2></div>
    <p style="text-align:center;">Archives de la commission, telles qu'elles ont été fournies (Word, PDF…). « Voir » ouvre le document directement dans Word (ou Excel, PowerPoint) pour le modifier ; les PDF s'ouvrent à l'écran.</p>
    <p style="margin:14px 0;font-weight:600;">
        <a href="<?= e($urlBase) ?>">Accueil</a>
        <?php $cum = ''; if ($rel !== '') foreach (explode('/', $rel) as $seg) { $cum .= ($cum === '' ? '' : '/') . $seg; echo ' &rsaquo; <a href="' . $lien('r', $cum) . '">' . e($seg) . '</a>'; } ?>
    </p>
    <?php if ($flash): ?><div class="alert <?= $flash[0] ? 'alert-succes' : 'alert-erreur' ?>"><?= e($flash[1]) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" data-no-ajax class="carte" style="margin-bottom:14px;padding:14px;">
        <input type="hidden" name="res_action" value="ajouter"><input type="hidden" name="r" value="<?= e($rel) ?>"><input type="hidden" name="jeton" value="<?= e(resJeton()) ?>">
        <strong>Ajouter un fichier dans <?= $rel === '' ? 'Accueil' : e(basename($rel)) ?></strong>
        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:8px;">
            <input type="file" name="fichiers[]" multiple required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp,.txt,.csv,.png,.jpg,.jpeg,.zip" style="flex:1;min-width:200px;">
            <button class="btn btn-primaire btn-sm" onclick="this.textContent='Envoi en cours…';">Ajouter</button>
        </div>
        <small style="color:#777;">Word, Excel, PowerPoint, PDF, images… 15 Mo maximum par fichier.</small>
    </form>
    <input type="search" id="resFiltre" placeholder="Rechercher dans ce dossier…" style="width:100%;margin-bottom:12px;" autocomplete="off">
    <div class="carte" style="padding:6px 14px;">
        <?php foreach ($dossiers as $n): $r = ($rel === '' ? '' : $rel . '/') . $n; ?>
        <div class="resl" data-n="<?= e(mb_strtolower($n)) ?>" style="display:flex;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee;">
            <span style="font-size:1.4rem;">📁</span><a href="<?= $lien('r', $r) ?>" style="font-weight:700;flex:1;word-break:break-word;"><?= e($n) ?></a>
        </div>
        <?php endforeach; ?>
        <?php foreach ($fichiers as $n): $r = ($rel === '' ? '' : $rel . '/') . $n; $app = resAppOffice($n); $aj = $ajoutes[$n] ?? null; $tailleF = $aj ? (int)$aj['taille'] : filesize($dossier . '/' . $n); ?>
        <div class="resl" data-n="<?= e(mb_strtolower($n)) ?>" style="display:flex;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee;flex-wrap:wrap;">
            <span style="font-size:1.4rem;"><?= resIcone($n) ?></span>
            <span style="flex:1;min-width:150px;word-break:break-word;"><?= e($n) ?><br><small style="color:#777;"><?= e(resTaille($tailleF)) ?><?php if ($aj): ?> &middot; ajouté le <?= e(date('d/m/Y', strtotime($aj['created_at']))) ?><?= $aj['ajoute_par'] ? ' par ' . e($aj['ajoute_par']) : '' ?><?php endif; ?></small></span>
            <?php if ($app): $pub = resUrlPublique($r); $nomApp = ['word' => 'Word', 'excel' => 'Excel', 'powerpoint' => 'PowerPoint'][$app]; ?>
                <a class="btn btn-or btn-sm" href="<?= e('ms-' . $app . ':ofe|u|' . $pub) ?>" title="S'ouvre directement dans <?= $nomApp ?> pour modifier">Voir (<?= $nomApp ?>)</a>
                <a class="btn btn-outline btn-sm" href="<?= e('https://view.officeapps.live.com/op/view.aspx?src=' . rawurlencode($pub)) ?>" target="_blank" rel="noopener" title="Aperçu dans le navigateur, sans Word">Aperçu</a>
            <?php else: ?>
                <a class="btn btn-or btn-sm" href="<?= $lien('v', $r) ?>" target="_blank" rel="noopener">Voir</a>
            <?php endif; ?>
            <a class="btn btn-primaire btn-sm" href="<?= $lien('d', $r) ?>">Télécharger</a>
            <?php if ($aj && $peutSupprimer): ?><form method="post" data-no-ajax style="margin:0;" onsubmit="return confirm('Supprimer ce fichier des archives ?')"><input type="hidden" name="res_action" value="supprimer"><input type="hidden" name="r" value="<?= e($rel) ?>"><input type="hidden" name="id" value="<?= (int)$aj['id'] ?>"><input type="hidden" name="jeton" value="<?= e(resJeton()) ?>"><button class="btn btn-danger btn-sm">Supprimer</button></form><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (!$dossiers && !$fichiers): ?><p style="padding:14px;text-align:center;">Dossier vide.</p><?php endif; ?>
    </div>
</div></section>
<script>
(function(){var f=document.getElementById('resFiltre');if(!f)return;f.addEventListener('input',function(){var q=f.value.toLowerCase().trim();[].forEach.call(document.querySelectorAll('.resl'),function(l){l.style.display=(!q||l.getAttribute('data-n').indexOf(q)>=0)?'flex':'none'})})})();
</script>
    <?php
}
/** Gère les téléchargements (?d= / ?v=) : à appeler avant tout affichage. */
function resTraiterTelechargement() {
    if (isset($_GET['d'])) resTelecharger((string)$_GET['d'], 'download');
    if (isset($_GET['v'])) resTelecharger((string)$_GET['v'], 'inline');
}
