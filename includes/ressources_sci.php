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
/** Envoie le fichier tel quel (jamais interprété par le navigateur ; les PDF s'ouvrent à l'écran). */
function resTelecharger($rel, $inlinePdf = false) {
    $f = resResoudre($rel);
    if (!$f || !is_file($f)) { http_response_code(404); die('Fichier introuvable.'); }
    $nom = basename($f);
    $pdf = strtolower(pathinfo($nom, PATHINFO_EXTENSION)) === 'pdf';
    $types = ['docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'pdf' => 'application/pdf'];
    $type = $types[strtolower(pathinfo($nom, PATHINFO_EXTENSION))] ?? 'application/octet-stream';
    while (ob_get_level()) ob_end_clean();
    header('X-Robots-Tag: noindex, nofollow');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: sandbox");
    header('Content-Type: ' . $type);
    header('Content-Length: ' . filesize($f));
    header('Content-Disposition: ' . (($pdf && $inlinePdf) ? 'inline' : 'attachment') . "; filename=\"" . str_replace(['"', '\\', "\r", "\n"], '_', $nom) . "\"; filename*=UTF-8''" . rawurlencode($nom));
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
    <div class="section-titre"><span class="eyebrow">Commission scientifique</span><h2>Documents de travail</h2></div>
    <p style="text-align:center;">Fichiers à utiliser pour le travail de la commission, tels qu'ils ont été fournis (Word, PDF…). Appuyez sur un fichier pour le télécharger.</p>
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
        <?php foreach ($fichiers as $n): $r = ($rel === '' ? '' : $rel . '/') . $n; $pdf = strtolower(pathinfo($n, PATHINFO_EXTENSION)) === 'pdf'; ?>
        <div class="resl" data-n="<?= e(mb_strtolower($n)) ?>" style="display:flex;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee;flex-wrap:wrap;">
            <span style="font-size:1.4rem;"><?= resIcone($n) ?></span>
            <span style="flex:1;min-width:150px;word-break:break-word;"><?= e($n) ?><br><small style="color:#777;"><?= e(resTaille(filesize($dossier . '/' . $n))) ?></small></span>
            <?php if ($pdf): ?><a class="btn btn-outline btn-sm" href="<?= $lien('v', $r) ?>" target="_blank" rel="noopener">Voir</a><?php endif; ?>
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
    if (isset($_GET['d'])) resTelecharger((string)$_GET['d'], false);
    if (isset($_GET['v'])) resTelecharger((string)$_GET['v'], true);
}
