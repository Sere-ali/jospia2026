<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
require_once __DIR__ . '/../includes/presentation_admin.php';

$contenuPres = presAdminLire();
$stats = presAdminChiffres();
$slidesAdmin = presAdminDiapositives($contenuPres, $stats);

/** Mots révélés un à un (masque + glissement), pour l'effet « titre cinéma ». */
function mots($s, $base = 0) {
    $o = ''; $i = $base;
    foreach (preg_split('/\s+/', trim($s)) as $m) {
        $t = ($m === '15e') ? '15<sup>e</sup>' : e($m);
        $o .= '<span class="w"><i style="--d:' . $i++ . '">' . $t . '</i></span> ';
    }
    return $o;
}
function liTxt($r, $gras) {
    $t = e($r);
    if ($gras && ($p = strpos($r, ' : ')) !== false) { $t = '<strong>' . e(substr($r, 0, $p + 2)) . '</strong>' . e(substr($r, $p + 2)); }
    return nl2br($t);
}

/** Diapositive de liste : les points apparaissent un à un, le point courant est mis en avant. */
function afficherListe($num, $nom, $res, $gras) { ?>
  <section class="slide liste" data-steps="<?= count($res) + 1 ?>"><div class="cadre">
    <header class="tete">
      <?php if ($num !== ''): ?><b class="num a" style="--d:0"><?= e(rtrim($num, '-')) ?></b><?php endif; ?>
      <h2><?= mots($nom, 1) ?></h2>
    </header>
    <ul class="pts">
      <?php foreach ($res as $k => $r): ?><li style="--k:<?= $k ?>"><i></i><span><?= liTxt($r, $gras) ?></span></li><?php endforeach; ?>
    </ul>
  </div></section>
<?php }

/** Diapositive de chiffres clés : compteurs animés. */
function afficherChiffres($nom, $res) { ?>
  <section class="slide chiffres" data-steps="1"><div class="cadre">
    <header class="tete"><h2><?= mots($nom, 0) ?></h2></header>
    <div class="cartes">
      <?php foreach ($res as $k => $r): $p = strrpos($r, ' : '); $lib = $p === false ? $r : substr($r, 0, $p); $val = $p === false ? '' : trim(substr($r, $p + 3));
          $n = (int)preg_replace('/\D+/', '', $val); ?>
      <div class="carte a" style="--d:<?= 2 + $k ?>"><b data-n="<?= $n ?>">0</b><span><?= e($lib) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div></section>
<?php }

/** Diapositive « comparaison » : une variante en grand à la fois, puis la vue d'ensemble. */
function afficherVariantes($titre, $dossier, $cle, $vars, $ext = 'webp', $paysage = false) {
    $n = count($vars);
    [$kick, $reste] = array_pad(explode(' : ', $titre, 2), 2, '');
    ?>
  <section class="slide var <?= $paysage ? 'land' : 'port' ?> n<?= $n ?>" data-steps="<?= $n + 1 ?>"><div class="cadre">
    <h2 class="titre"><em><?= mots($kick . ' :', 0) ?></em> <?= mots($reste, 2) ?></h2>
    <div class="hero a" style="--d:3">
      <?php foreach ($vars as $k => [$f, $lib, $coul, $hex]):
          $chem = 'assets/img/pres/' . $dossier . '/' . $cle . '_' . $f . '.' . $ext; ?>
      <img class="hv" src="<?= BASE_URL ?>/<?= e($chem) ?>?v=<?= (int)@filemtime(__DIR__ . '/../' . $chem) ?>" alt="<?= e($titre) ?> — <?= e($coul) ?>">
      <?php endforeach; ?>
    </div>
    <div class="panneau a" style="--d:4">
      <div class="infos">
        <?php foreach ($vars as $k => [$f, $lib, $coul, $hex]): ?>
        <div class="info"><small><?= e(mb_strtoupper($lib, 'UTF-8')) ?></small><h3><?= e($coul) ?></h3><div class="sw" style="--c:<?= e($hex) ?>"></div></div>
        <?php endforeach; ?>
      </div>
      <div class="vign">
        <?php foreach ($vars as $k => [$f, $lib, $coul, $hex]):
            $chem = 'assets/img/pres/' . $dossier . '/' . $cle . '_' . $f . '.' . $ext; ?>
        <button type="button" class="vg" data-k="<?= $k ?>" style="--c:<?= e($hex) ?>"><img src="<?= BASE_URL ?>/<?= e($chem) ?>?v=<?= (int)@filemtime(__DIR__ . '/../' . $chem) ?>" alt=""><span><?= $k === 0 && stripos($lib, 'actuelle') !== false ? 'Actuelle' : 'N°' . ($k + (stripos($vars[0][1], 'actuelle') !== false ? 0 : 1)) ?></span></button>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="grille">
      <?php foreach ($vars as $k => [$f, $lib, $coul, $hex]):
          $chem = 'assets/img/pres/' . $dossier . '/' . $cle . '_' . $f . '.' . $ext; ?>
      <figure style="--k:<?= $k ?>;--c:<?= e($hex) ?>"><img src="<?= BASE_URL ?>/<?= e($chem) ?>?v=<?= (int)@filemtime(__DIR__ . '/../' . $chem) ?>" alt=""><figcaption><b><?= e($lib) ?></b><span><?= e($coul) ?></span></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </div></section>
<?php }

$AC = ['actuel', 'Version actuelle', 'Orange (initial)', '#F28B1E'];
$BT = [['petrole', 'Proposition 1', 'Bleu pétrole', '#0A6B84'], ['vert', 'Proposition 2', 'Vert profond', '#3F6B52'], ['nuit', 'Proposition 3', 'Bleu nuit', '#2A44A0']];
$BA = array_merge([$AC], $BT);
$DS = array_merge([['actuel', 'Version actuelle', 'Vert et or (initial)', '#C89B2C']], $BT);
$DC = [['actuel', 'Version actuelle', 'Vert (initial)', '#2E9A4C'], ['a', 'Proposition 1', 'Vagues vert et orange', '#3CB34A'], ['b', 'Proposition 2', 'Bande latérale vert profond', '#3F6B52'], ['c', 'Proposition 3', 'Rubans bleu pétrole', '#0A6B84']];
$OF = [['petrole', 'Proposition 1', 'Bleu pétrole', '#0A6B84'], ['bordeaux', 'Proposition 2', 'Bordeaux', '#A63A58'], ['anthracite', 'Proposition 3', 'Anthracite', '#6A7077']];
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Commission Administration — Bataille de Kirina — JOSPIA 2026</title>
<style>
@font-face{font-family:'Barlow';font-weight:800;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-latin-800-normal.woff2) format('woff2')}
@font-face{font-family:'Barlow';font-weight:700;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-latin-700-normal.woff2) format('woff2')}
@font-face{font-family:'BarlowSC';font-weight:600;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-semi-condensed-latin-600-normal.woff2) format('woff2')}
:root{--s:1;--o:#FF7A1A;--o2:#FFB36B;--t:#12C2B0;--bg:#03141A;--bg2:#062F3A;--tx:#F4FAF9;--mu:#9DB9BD}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;background:#010a0d;color:var(--tx);font-family:'Barlow','Inter',system-ui,Arial,sans-serif;overscroll-behavior:none;touch-action:manipulation;-webkit-tap-highlight-color:transparent}
button{font-family:inherit}
#stage{position:absolute;left:50%;top:50%;width:1920px;height:1080px;margin:-540px 0 0 -960px;transform:scale(var(--s));transform-origin:center;overflow:hidden;background:var(--bg);isolation:isolate}
/* ---------- décor animé ---------- */
.bg{position:absolute;inset:0;background:radial-gradient(1200px 800px at 82% 8%,#0b4a58 0,transparent 60%),radial-gradient(1000px 800px at 5% 100%,#0a3a32 0,transparent 60%),linear-gradient(160deg,#021015,#062a34 55%,#03141a);z-index:-3}
.grid{position:absolute;inset:-200px;z-index:-2;opacity:.16;background-image:linear-gradient(rgba(120,220,215,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(120,220,215,.5) 1px,transparent 1px);background-size:96px 96px;transform:perspective(900px) rotateX(58deg) translateY(0) scale(1.8);transform-origin:center 85%;animation:gr 14s linear infinite;-webkit-mask-image:linear-gradient(transparent 5%,#000 60%);mask-image:linear-gradient(transparent 5%,#000 60%)}
@keyframes gr{to{background-position:0 96px}}
.orb{position:absolute;border-radius:50%;filter:blur(90px);z-index:-2;opacity:.55;animation:orb 16s ease-in-out infinite}
.orb.o1{width:760px;height:760px;right:-200px;top:-260px;background:radial-gradient(circle,rgba(255,122,26,.8),transparent 70%)}
.orb.o2{width:680px;height:680px;left:-220px;bottom:-260px;background:radial-gradient(circle,rgba(18,194,176,.7),transparent 70%);animation-delay:-8s}
@keyframes orb{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(-90px,60px) scale(1.15)}}
#ptc{position:absolute;inset:0;z-index:-1;pointer-events:none}
.balayage{position:absolute;inset:0;z-index:-1;background:linear-gradient(105deg,transparent 42%,rgba(255,255,255,.07) 50%,transparent 58%);background-size:250% 100%;animation:bl 9s ease-in-out infinite;pointer-events:none}
@keyframes bl{0%{background-position:130% 0}55%,100%{background-position:-60% 0}}
/* ---------- éléments fixes ---------- */
.logo{position:absolute;left:56px;top:34px;height:84px;padding:6px 18px;background:#fff;border-radius:42px;box-shadow:0 10px 40px rgba(0,0,0,.45),0 0 0 2px rgba(255,255,255,.12);z-index:20;display:flex;align-items:center;animation:desc 1s cubic-bezier(.2,.9,.2,1) both}
.logo img{height:100%;width:auto;display:block}
@keyframes desc{from{opacity:0;transform:translateY(-60px)}}
.tag{position:absolute;right:56px;top:46px;z-index:20;display:flex;align-items:center;gap:16px;font-weight:700;letter-spacing:.2em;font-size:24px;color:var(--o2);text-transform:uppercase}
.tag::before{content:"";width:46px;height:3px;background:var(--o);box-shadow:0 0 14px var(--o)}
.prog{position:absolute;left:0;bottom:0;height:7px;width:100%;z-index:30;background:rgba(255,255,255,.08)}
.prog b{display:block;height:100%;width:0;background:linear-gradient(90deg,var(--t),var(--o));box-shadow:0 0 18px var(--o);transition:width .8s cubic-bezier(.3,0,.2,1)}
/* ---------- transition « balayage » ---------- */
#wipe{position:absolute;inset:0;z-index:40;pointer-events:none;overflow:hidden}
#wipe b{position:absolute;top:-15%;bottom:-15%;left:-80%;width:75%;transform:skewX(-16deg)}
#wipe b:nth-child(1){background:linear-gradient(90deg,#ff5e00,#ff9a3d)}
#wipe b:nth-child(2){background:linear-gradient(90deg,#0b6b7a,#12c2b0)}
#wipe b:nth-child(3){background:#031a21}
#wipe.go b{animation:wp 1.15s cubic-bezier(.75,0,.25,1) both}
#wipe.go b:nth-child(2){animation-delay:.09s}
#wipe.go b:nth-child(3){animation-delay:.18s}
@keyframes wp{from{transform:skewX(-16deg) translateX(0)}to{transform:skewX(-16deg) translateX(330%)}}
/* ---------- diapositives ---------- */
.slide{position:absolute;inset:0;display:none}
.slide.actif{display:block}
.cadre{position:absolute;inset:0;padding:0 90px}
.w{display:inline-block;overflow:hidden;vertical-align:top;padding:.2em .05em .1em;margin:-.2em -.05em -.1em}
.w i{display:inline-block;font-style:normal;transform:translateY(118%)}
.slide.actif .w i{animation:mot 1s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.5s + var(--d)*.11s)}
@keyframes mot{from{transform:translateY(118%) rotate(5deg)}to{transform:none}}
.a{opacity:0}
.slide.actif .a{animation:ap 1s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.6s + var(--d,0)*.12s)}
@keyframes ap{from{opacity:0;transform:translateY(70px) scale(.96)}to{opacity:1;transform:none}}
.tete{position:absolute;left:90px;right:90px;top:158px;display:flex;align-items:center;gap:34px}
.num{flex:none;display:flex;align-items:center;justify-content:center;min-width:104px;height:104px;border-radius:26px;background:linear-gradient(135deg,var(--o),#ff4d00);font-size:64px;font-weight:800;box-shadow:0 14px 40px rgba(255,98,0,.45)}
.tete h2{font-size:84px;line-height:1.02;font-weight:800;text-transform:uppercase;letter-spacing:.01em;text-shadow:0 6px 30px rgba(0,0,0,.4)}
/* titre */
.intro .cadre,.fin .cadre{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding-top:60px}
.intro .k{font-size:34px;font-weight:700;letter-spacing:.34em;color:var(--o2);text-transform:uppercase;margin-bottom:26px}
.intro h1,.fin h1{font-size:210px;line-height:.92;font-weight:800;text-transform:uppercase;letter-spacing:.01em;text-shadow:0 10px 50px rgba(0,0,0,.45)}
.intro h1 .l2{display:block;color:transparent;-webkit-text-stroke:3px var(--o);text-shadow:0 0 50px rgba(255,122,26,.45)}
.intro h1 .l2 .w i{color:transparent}
.intro .ss{margin-top:44px;font-size:46px;font-weight:700;color:#d6ecec;max-width:1500px;line-height:1.2}
.intro .pill{margin-top:40px;display:inline-flex;gap:14px;align-items:center;padding:14px 38px;border-radius:60px;background:rgba(255,122,26,.14);border:2px solid var(--o);font-size:34px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--o2)}
.intro .pill::before{content:"";width:14px;height:14px;border-radius:50%;background:var(--o);box-shadow:0 0 16px var(--o)}
.ligne{width:0;height:6px;border-radius:3px;background:linear-gradient(90deg,var(--t),var(--o));box-shadow:0 0 20px var(--o);margin-top:48px}
.slide.actif .ligne{animation:li 1.4s .9s cubic-bezier(.2,.9,.2,1) both}
@keyframes li{to{width:620px}}
.fin h1{font-size:300px}
.fin p{margin-top:34px;font-size:56px;font-weight:700;color:#d6ecec}
#fete{position:absolute;inset:0;z-index:35;pointer-events:none}
/* listes */
.pts{position:absolute;left:90px;right:90px;top:330px;list-style:none;display:flex;flex-direction:column;gap:30px}
.pts li{display:flex;gap:34px;align-items:flex-start;font-size:54px;line-height:1.2;font-weight:700;color:#e9f6f5;opacity:0;transform:translateX(-120px);transition:opacity .8s cubic-bezier(.2,.9,.2,1),transform .9s cubic-bezier(.2,.9,.2,1),filter .6s;filter:blur(8px)}
.pts li.on{opacity:.38;transform:none;filter:none}
.pts li.cur{opacity:1}
.pts li i{flex:none;width:26px;height:26px;margin-top:20px;border-radius:7px;background:var(--o);transform:rotate(45deg);box-shadow:0 0 22px rgba(255,122,26,.8)}
.pts li strong{color:var(--o2);font-weight:800}
.pts li.cur span{text-shadow:0 0 40px rgba(18,194,176,.35)}
/* chiffres */
.cartes{position:absolute;left:90px;right:90px;top:360px;display:grid;grid-template-columns:repeat(3,1fr);gap:40px}
.carte{padding:48px 40px;border-radius:30px;background:linear-gradient(160deg,rgba(255,255,255,.1),rgba(255,255,255,.03));border:2px solid rgba(255,255,255,.14);backdrop-filter:blur(6px);box-shadow:0 20px 60px rgba(0,0,0,.35)}
.carte b{display:block;font-size:150px;line-height:1;font-weight:800;color:var(--o);text-shadow:0 0 40px rgba(255,122,26,.5)}
.carte span{display:block;margin-top:18px;font-size:42px;font-weight:700;color:#d6ecec;line-height:1.15}
/* comparaisons badges / diplômes */
.titre{position:absolute;left:90px;top:152px;font-size:60px;line-height:1.05;font-weight:800;text-transform:uppercase;white-space:nowrap}
.titre em{font-style:normal;color:var(--o)}
.var .hero{position:absolute;perspective:1600px}
.var .hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:contain;border-radius:22px;opacity:0;transform:translateX(90px) scale(.92) rotateY(-14deg);transition:opacity .7s,transform .9s cubic-bezier(.2,.9,.2,1);box-shadow:0 40px 110px rgba(0,0,0,.65),0 0 0 3px rgba(255,255,255,.1);background:#fff}
.var .hero img.on{opacity:1;transform:none;animation:fl 7s ease-in-out infinite}
.var .hero img.off{opacity:0;transform:translateX(-90px) scale(.92) rotateY(14deg)}
@keyframes fl{0%,100%{translate:0 0}50%{translate:0 -10px}}
.var.port .hero{left:110px;top:240px;width:495px;height:788px}
.var.port .panneau{position:absolute;left:720px;right:90px;top:240px;height:788px}
.var.land .hero{left:90px;top:250px;width:1110px;height:784px}
.var.land .panneau{position:absolute;left:1250px;right:80px;top:250px;height:784px}
.infos{position:relative;height:340px}
.info{position:absolute;inset:0;opacity:0;transform:translateY(40px);transition:opacity .6s,transform .8s cubic-bezier(.2,.9,.2,1)}
.info.on{opacity:1;transform:none}
.info small{display:inline-block;padding:10px 26px;border-radius:40px;background:var(--o);font-size:30px;font-weight:800;letter-spacing:.14em;box-shadow:0 10px 30px rgba(255,98,0,.4)}
.info h3{margin-top:26px;font-size:104px;line-height:1;font-weight:800;text-transform:uppercase}
.land .info h3{font-size:68px}
.sw{margin-top:28px;height:22px;width:0;border-radius:11px;background:var(--c);box-shadow:0 0 34px var(--c)}
.info.on .sw{animation:sw 1s .2s cubic-bezier(.2,.9,.2,1) both}
@keyframes sw{to{width:100%}}
.vign{position:absolute;left:0;right:0;bottom:0;display:flex;gap:26px}
.land .vign{flex-wrap:wrap;gap:20px}
.vg{position:relative;border:0;padding:0;background:none;cursor:pointer;border-radius:16px;transition:transform .5s cubic-bezier(.2,.9,.2,1),opacity .4s;opacity:.55;flex:1 1 0}
.land .vg{flex:0 0 calc(50% - 10px)}
.vg img{display:block;width:100%;height:auto;border-radius:14px;box-shadow:0 12px 30px rgba(0,0,0,.5);border:4px solid transparent;background:#fff}
.vg span{display:block;text-align:center;padding:8px 0 0;font-size:28px;font-weight:700;color:#cfe7e8}
.vg.on span{color:var(--o2)}
.vg.on{opacity:1;transform:translateY(-12px) scale(1.04)}
.vg.on img{border-color:var(--o);box-shadow:0 0 0 2px var(--o),0 0 40px rgba(255,122,26,.65)}
.var .grille{position:absolute;left:90px;right:90px;top:250px;bottom:50px;display:flex;justify-content:center;align-items:center;gap:36px;pointer-events:none}
.grille figure{position:relative;flex:0 1 auto;opacity:0;transform:translateY(90px) scale(.9);transition:opacity .7s calc(var(--k)*.14s),transform .9s calc(var(--k)*.14s) cubic-bezier(.2,.9,.2,1)}
.grille img{display:block;height:630px;width:auto;max-width:100%;border-radius:20px;box-shadow:0 30px 80px rgba(0,0,0,.6);border:4px solid var(--c);background:#fff}
.land .grille{flex-wrap:wrap;align-content:center}
.land .grille img{height:auto;width:880px}
.land.n3 .grille img{width:550px;height:auto}
.grille figcaption{display:flex;flex-direction:column;align-items:center;padding:16px 8px 0;font-size:36px;line-height:1.12;text-align:center}
.land .grille figcaption{position:absolute;left:14px;top:14px;flex-direction:row;gap:12px;padding:8px 22px;border-radius:30px;background:rgba(3,20,26,.88);font-size:28px;white-space:nowrap}
.land .grille figcaption span{display:none}
.grille figcaption b{font-weight:800;color:var(--o2)}
.grille figcaption span{font-weight:700}
.land.n4 .grille{gap:22px 26px;left:380px;right:380px}
.land.n4 .grille figure img{width:530px;height:auto}
.var.ov .hero,.var.ov .panneau{opacity:0!important;transition:opacity .5s;pointer-events:none}
.var.ov .grille{pointer-events:auto}
.var.ov .grille figure{opacity:1;transform:none}
/* contrôles */
.cmd{position:fixed;left:0;right:0;bottom:0;z-index:60;display:flex;justify-content:space-between;align-items:center;padding:14px 22px;opacity:0;transition:opacity .5s;pointer-events:none}
body.actifs .cmd{opacity:1;pointer-events:auto}
.cmd .g{display:flex;gap:10px;align-items:center}
.cmd button,.cmd a{width:52px;height:52px;border-radius:50%;border:0;background:rgba(255,255,255,.14);backdrop-filter:blur(8px);color:#fff;font-size:22px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:.2s}
.cmd button:hover,.cmd a:hover{background:var(--o);transform:scale(1.1)}
.cmd button:disabled{opacity:.3;pointer-events:none}
.cmd .cp{min-width:70px;text-align:right;font-weight:800;color:#cfe7e8;font-size:18px}
body.cache{cursor:none}
</style>
</head>
<body>
<div id="stage">
  <div class="bg"></div><div class="grid"></div><div class="orb o1"></div><div class="orb o2"></div><canvas id="ptc" width="1920" height="1080"></canvas><div class="balayage"></div>
  <div class="logo"><img src="<?= BASE_URL ?>/assets/img/pres_bandeau.png" alt="AEEMCI — Secrétariat Régional Abidjan Nord — Sous-comités Anyama 1 &amp; 2 — JOSPIA"></div>
  <div class="tag">Bataille de Kirina</div>

  <section class="slide intro" data-steps="1"><div class="cadre">
    <div class="k a" style="--d:0">Bataille de Kirina</div>
    <h1><?= mots('COMMISSION', 1) ?><span class="l2"><?= mots('ADMINISTRATION', 3) ?></span></h1>
    <div class="ss a" style="--d:9">Réalisations, travaux non effectués et difficultés</div>
    <div class="ligne"></div>
  </div></section>

  <?php
  afficherVariantes('RÉALISATIONS : LE BADGE DES SÉMINARISTES', 'badges', 'seminariste', $BA);
  afficherVariantes('RÉALISATIONS : LE BADGE DES MEMBRES DE COMMISSION', 'badges', 'commission', $BA);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES MEMBRES DE COMMISSION', 'diplomes', 'commission', $DC, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DU SÉMINARISTE', 'diplomes', 'seminariste', $DS, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES OFFICIELS', 'diplomes', 'officiel', $OF, 'jpg', true);
  foreach ($slidesAdmin as [$num, $nom, $res, $gras, $deux]) {
      if ($nom === 'LA COMMISSION EN CHIFFRES') { afficherChiffres($nom, $res); }
      else { afficherListe($num, $nom, $res, $gras); }
  }
  ?>

  <section class="slide fin" data-steps="1"><div class="cadre">
    <h1><?= mots('MERCI', 0) ?></h1>
    <p class="a" style="--d:6">Je vous remercie pour votre aimable attention.</p>
    <div class="ligne"></div>
  </div></section>

  <canvas id="fete" width="1920" height="1080"></canvas>
  <div id="wipe"><b></b><b></b><b></b></div>
  <div class="prog"><b id="prog"></b></div>
</div>
<div class="cmd">
  <div class="g"><button id="prec" type="button" aria-label="Précédent">&#8592;</button><button id="suiv" type="button" aria-label="Suivant">&#8594;</button></div>
  <div class="g"><?php if (!defined('PRESENTATION_PUBLIQUE')): ?><a href="<?= BASE_URL ?>/admin/administration" title="Quitter" aria-label="Quitter">&#10005;</a><?php endif; ?><button id="plein" type="button" title="Plein écran" aria-label="Plein écran">&#x26F6;</button><div class="cp" id="cpt"></div></div>
</div>
<script>
(function(){
  var stage=document.getElementById('stage');
  function echelle(){var s=Math.min(innerWidth/1920,innerHeight/1080);stage.style.setProperty('--s',s)}
  echelle();addEventListener('resize',echelle);addEventListener('orientationchange',echelle);

  /* particules lumineuses */
  var pc=document.getElementById('ptc'),px=pc.getContext('2d'),PT=[];
  for(var i=0;i<60;i++)PT.push({x:Math.random()*1920,y:Math.random()*1080,r:1.5+Math.random()*4,v:.15+Math.random()*.6,a:.15+Math.random()*.45,c:Math.random()<.5?'255,140,50':'80,220,205',p:Math.random()*6});
  (function anim(t){px.clearRect(0,0,1920,1080);
    PT.forEach(function(p){p.y-=p.v;p.x+=Math.sin(t/2000+p.p)*.35;if(p.y<-10){p.y=1090;p.x=Math.random()*1920}
      px.beginPath();px.fillStyle='rgba('+p.c+','+p.a+')';px.shadowColor='rgba('+p.c+',.9)';px.shadowBlur=14;px.arc(p.x,p.y,p.r,0,6.283);px.fill()});
    requestAnimationFrame(anim)})(0);

  var sl=[].slice.call(document.querySelectorAll('.slide')),n=sl.length,c=-1,st=0,busy=false;
  var wipe=document.getElementById('wipe'),prog=document.getElementById('prog'),cpt=document.getElementById('cpt');

  function compter(s){
    [].forEach.call(s.querySelectorAll('[data-n]'),function(b,k){
      var cible=parseInt(b.getAttribute('data-n'),10)||0,t0=null,delai=900+k*200;
      b.textContent='0';
      setTimeout(function(){(function f(t){if(t0===null)t0=t;var p=Math.min(1,(t-t0)/1500),e=1-Math.pow(1-p,4);b.textContent=Math.round(cible*e).toLocaleString('fr-FR');if(p<1)requestAnimationFrame(f)})(performance.now())},delai)
    });
  }
  function maj(){
    var s=sl[c],ns=parseInt(s.getAttribute('data-steps'),10);
    if(s.classList.contains('var')){
      var k=s.querySelectorAll('.hv').length,idx=Math.min(st,k-1);
      [].forEach.call(s.querySelectorAll('.hv'),function(im,i){im.classList.toggle('on',i===idx);im.classList.toggle('off',i<idx)});
      [].forEach.call(s.querySelectorAll('.info'),function(o,i){o.classList.toggle('on',i===idx)});
      [].forEach.call(s.querySelectorAll('.vg'),function(o,i){o.classList.toggle('on',i===idx)});
      s.classList.toggle('ov',st>=k);
    }
    if(s.classList.contains('liste')){
      var li=s.querySelectorAll('li');
      [].forEach.call(li,function(o,i){o.classList.toggle('on',st>=i+1);o.classList.toggle('cur',st===i+1)});
    }
    document.getElementById('prec').disabled=(c===0&&st===0);
    document.getElementById('suiv').disabled=(c===n-1&&st>=ns-1);
  }
  function go(i,step,direct){
    if(i<0||i>=n)return;
    if(i===c){st=step;maj();return}
    function bascule(){
      sl.forEach(function(s,k){s.classList.toggle('actif',k===i)});
      c=i;st=step;maj();
      cpt.textContent=(i+1)+' / '+n;prog.style.width=((i+1)/n*100)+'%';
      try{history.replaceState(null,'','#'+(i+1))}catch(e){}
      compter(sl[i]);
      if(sl[i].classList.contains('fin'))setTimeout(fete,1100);
    }
    if(direct||c<0){bascule();return}
    busy=true;wipe.className='';void wipe.offsetWidth;wipe.className='go';
    setTimeout(bascule,520);setTimeout(function(){wipe.className='';busy=false},1500);
  }
  function suivant(){if(busy)return;var max=parseInt(sl[c].getAttribute('data-steps'),10)-1;if(st<max){st++;maj()}else go(c+1,0)}
  function precedent(){if(busy)return;if(st>0){st--;maj()}else if(c>0){go(c-1,parseInt(sl[c-1].getAttribute('data-steps'),10)-1)}}
  document.getElementById('prec').onclick=precedent;document.getElementById('suiv').onclick=suivant;
  document.addEventListener('keydown',function(e){
    if(['ArrowRight','PageDown',' ','Enter'].indexOf(e.key)>=0){e.preventDefault();suivant()}
    else if(['ArrowLeft','PageUp','Backspace'].indexOf(e.key)>=0){e.preventDefault();precedent()}
    else if(e.key==='Home')go(0,0);else if(e.key==='End')go(n-1,0);
    else if(e.key==='f'||e.key==='F')pl();
  });
  /* clic sur une vignette = afficher cette variante */
  document.addEventListener('click',function(e){
    var v=e.target.closest&&e.target.closest('.vg');if(v&&!busy){st=parseInt(v.getAttribute('data-k'),10);maj()}
  });
  var x0=null,y0=null;
  document.addEventListener('touchstart',function(e){x0=e.touches[0].clientX;y0=e.touches[0].clientY},{passive:true});
  document.addEventListener('touchend',function(e){if(x0===null)return;var dx=e.changedTouches[0].clientX-x0,dy=e.changedTouches[0].clientY-y0;x0=null;
    if(Math.abs(dx)>60&&Math.abs(dx)>Math.abs(dy)*1.5){dx<0?suivant():precedent()}},{passive:true});
  function pl(){var d=document.documentElement;if(!document.fullscreenElement){(d.requestFullscreen||d.webkitRequestFullscreen||function(){}).call(d)}else{(document.exitFullscreen||document.webkitExitFullscreen).call(document)}}
  document.getElementById('plein').onclick=pl;
  /* effet de profondeur sur l'image principale */
  document.addEventListener('mousemove',function(e){
    var x=e.clientX/innerWidth-.5,y=e.clientY/innerHeight-.5;
    [].forEach.call(document.querySelectorAll('.slide.actif .hero'),function(h){h.style.transform='rotateY('+(x*7)+'deg) rotateX('+(-y*5)+'deg)'});
    document.body.classList.add('actifs');document.body.classList.remove('cache');clearTimeout(document._m);document._m=setTimeout(function(){document.body.classList.remove('actifs');document.body.classList.add('cache')},3000);
  });
  /* feu d'artifice de fin */
  var fc=document.getElementById('fete'),fx=fc.getContext('2d'),F=[],run=false,col=['255,122,26','18,194,176','255,255,255','255,179,107'];
  function fete(){
    for(var b=0;b<9;b++)(function(b){setTimeout(function(){var cx=300+Math.random()*1320,cy=200+Math.random()*420;
      for(var k=0;k<70;k++){var a=Math.random()*6.283,v=3+Math.random()*7;F.push({x:cx,y:cy,vx:Math.cos(a)*v,vy:Math.sin(a)*v,l:1,c:col[k%4]})}
      if(!run){run=true;bo()}},b*420)})(b);
  }
  function bo(){fx.clearRect(0,0,1920,1080);
    F=F.filter(function(p){return p.l>0});
    F.forEach(function(p){p.x+=p.vx;p.y+=p.vy;p.vy+=.12;p.vx*=.985;p.l-=.011;fx.beginPath();fx.fillStyle='rgba('+p.c+','+Math.max(p.l,0)+')';fx.shadowColor='rgba('+p.c+',1)';fx.shadowBlur=12;fx.arc(p.x,p.y,3,0,6.283);fx.fill()});
    if(F.length)requestAnimationFrame(bo);else run=false}
  var h=parseInt((location.hash||'').slice(1),10);
  go(h>=1&&h<=n?h-1:0,0,true);
})();
</script>
</body>
</html>
