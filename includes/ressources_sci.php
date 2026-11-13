<?php
/** Documents de travail de la commission scientifique (fichiers conservés tels quels, en Word, PDF, etc.). */
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
    $f = resResoudre($rel);
    if (!$f || !is_file($f)) { http_response_code(404); die('Fichier introuvable.'); }
    $nom = basename($f);
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
    header('Content-Length: ' . filesize($f));
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename=\"" . str_replace(['"', '\\', "\r", "\n"], '_', $nom) . "\"; filename*=UTF-8''" . rawurlencode($nom));
    header('Cache-Control: private, max-age=600');
    readfile($f);
    exit;
}
/** Affiche le dossier demandé (?r=) ; $urlBase = adresse de la page (sans paramètres). */
function resAfficher($urlBase) {
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
    natcasesort($dossiers); natcasesort($fichiers);
    $lien = function ($cle, $val) use ($urlBase) { return e($urlBase . '?' . $cle . '=' . rawurlencode($val)); };
    ?>
<section class="section"><div class="container" style="max-width:900px;">
    <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Archives</h2></div>
    <p style="text-align:center;">Archives de la commission, telles qu'elles ont été fournies (Word, PDF…). « Voir » ouvre le document directement dans Word (ou Excel, PowerPoint) pour le modifier ; les PDF s'ouvrent à l'écran.</p>
    <p style="margin:14px 0;font-weight:600;">
        <a href="<?= e($urlBase) ?>">Accueil</a>
        <?php $cum = ''; if ($rel !== '') foreach (explode('/', $rel) as $seg) { $cum .= ($cum === '' ? '' : '/') . $seg; echo ' &rsaquo; <a href="' . $lien('r', $cum) . '">' . e($seg) . '</a>'; } ?>
    </p>
    <input type="search" id="resFiltre" placeholder="Rechercher dans ce dossier…" style="width:100%;margin-bottom:12px;" autocomplete="off">
    <div class="carte" style="padding:6px 14px;">
        <?php foreach ($dossiers as $n): $r = ($rel === '' ? '' : $rel . '/') . $n; ?>
        <div class="resl" data-n="<?= e(mb_strtolower($n)) ?>" style="display:flex;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee;">
            <span style="font-size:1.4rem;">📁</span><a href="<?= $lien('r', $r) ?>" style="font-weight:700;flex:1;word-break:break-word;"><?= e($n) ?></a>
        </div>
        <?php endforeach; ?>
        <?php foreach ($fichiers as $n): $r = ($rel === '' ? '' : $rel . '/') . $n; $app = resAppOffice($n); ?>
        <div class="resl" data-n="<?= e(mb_strtolower($n)) ?>" style="display:flex;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee;flex-wrap:wrap;">
            <span style="font-size:1.4rem;"><?= resIcone($n) ?></span>
            <span style="flex:1;min-width:150px;word-break:break-word;"><?= e($n) ?><br><small style="color:#777;"><?= e(resTaille(filesize($dossier . '/' . $n))) ?></small></span>
            <?php if ($app): $pub = resUrlPublique($r); $nomApp = ['word' => 'Word', 'excel' => 'Excel', 'powerpoint' => 'PowerPoint'][$app]; ?>
                <a class="btn btn-or btn-sm" href="<?= e('ms-' . $app . ':ofe|u|' . $pub) ?>" title="S'ouvre directement dans <?= $nomApp ?> pour modifier">Voir (<?= $nomApp ?>)</a>
                <a class="btn btn-outline btn-sm" href="<?= e('https://view.officeapps.live.com/op/view.aspx?src=' . rawurlencode($pub)) ?>" target="_blank" rel="noopener" title="Aperçu dans le navigateur, sans Word">Aperçu</a>
            <?php else: ?>
                <a class="btn btn-or btn-sm" href="<?= $lien('v', $r) ?>" target="_blank" rel="noopener">Voir</a>
            <?php endif; ?>
            <a class="btn btn-primaire btn-sm" href="<?= $lien('d', $r) ?>">Télécharger</a>
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
