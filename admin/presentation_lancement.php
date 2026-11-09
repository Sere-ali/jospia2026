<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin', 'mg']);

// Contenu fourni par le Comité d'organisation (document « Grandes Activités & Innovations JOSPIA 2026 »), repris tel quel.
$sections = [
  ['I.', 'LES GRANDES ACTIVITÉS', [
    ['', 'CÉRÉMONIE DE LANCEMENT OFFICIEL DE JOSPIA 2026', '',
      ["Donner le coup d'envoi officiel de JOSPIA 2026, en dévoiler la vision, le thème, les ambitions et les grandes articulations, tout en mobilisant et fédérant l'ensemble des parties prenantes autour des enjeux et de la réussite de cette 15e édition."],
      'RÉSULTATS ATTENDUS',
      ["JOSPIA 2026 est officiellement lancé ;", "La vision, le thème et les objectifs de l'édition sont clairement compris ;", "Les parties prenantes sont mobilisées et engagées ;", "La visibilité et la notoriété de JOSPIA 2026 sont renforcées ;", "Une dynamique collective est créée pour assurer la réussite du séminaire."]],
    ['1.', 'CÉRÉMONIE OFFICIELLE DE JOSPIA 2026', '',
      ["Faire de la cérémonie officielle le temps fort institutionnel de JOSPIA 2026, réunissant séminaristes, parents, guides religieux, autorités politiques et administratives, structures de jeunesse, structures sœurs et partenaires autour du thème central et des enjeux de la jeunesse."],
      'RÉSULTATS ATTENDUS',
      ["Réunir les principaux acteurs autour des enjeux de l'AEEMCI ;", "Favoriser le dialogue entre militants, parents, guides religieux, autorités et structures de jeunesse ;", "Renforcer les liens entre les parties prenantes ;", "Consolider JOSPIA comme cadre de formation, de dialogue et de responsabilisation de la jeunesse."]],
    ['3-', 'LES BARROS SANS FILTRE', "Les espaces de parole, d'écoute et de transmission entre jeunes",
      ["Offrir aux jeunes un cadre d'échange adapté à leurs réalités, favorisant l'expression libre, le partage d'expériences et la transmission de repères islamiques pour mieux affronter les défis de la vie."],
      'RÉSULTAT ATTENDU',
      ["Des jeunes davantage écoutés, mieux orientés et mieux outillés pour faire face aux défis de leur génération, grâce à des repères islamiques solides et à une meilleure capacité de réflexion et de prise de décision."]],
    ['4-', 'CROSS POPULAIRE', '',
      ["Sensibiliser les populations aux valeurs islamiques, citoyennes et républicaines à travers une marche collective, tout en renforçant la visibilité des JOSPIA et de l'AEEMCI auprès de la population, des autorités et des partenaires."],
      'RÉSULTATS ATTENDUS',
      ["Une sensibilisation accrue de la population aux valeurs et aux messages véhiculés ;", "Une meilleure visibilité des JOSPIA et de l'AEEMCI dans la commune ;", "Une promotion des valeurs d'engagement, de travail, de discipline et de responsabilité citoyenne."]],
    ['5-', 'JEUX ET CONCOURS AL-ÎLM INTERSECTIONS', '',
      ["Renforcer les connaissances islamiques, académiques et managériales des militants à travers une compétition intersections, stimulant l'émulation intellectuelle et la saine compétitivité."],
      'RÉSULTATS ATTENDUS',
      ["Des militants mieux instruits et outillés dans les domaines islamique, académique et managérial ;", "Une saine émulation intellectuelle et une meilleure valorisation des connaissances entre les sections ;", "Un esprit de compétition, de cohésion et de dépassement de soi durablement renforcé entre les sections."]],
    ['6-', 'LA NUIT DES TALENTS', 'Révéler les talents, inspirer et édifier',
      ["Révéler et valoriser les talents des séminaristes à travers des expressions créatives, éducatives et porteuses de valeurs."],
      'RÉSULTATS ATTENDUS',
      ["Des talents révélés et valorisés dans un esprit d'humilité et de saine émulation ;", "Une créativité accrue et une fraternité renforcée entre les séminaristes ;", "Des messages éducatifs et spirituels transmis par des expressions originales."]],
  ]],
  ['II.', 'LES GRANDES INNOVATIONS', [
    ['', "HALAQAT AT-TA'LÎM", '',
      ["Renforcer les compétences coraniques des séminaristes à travers un enseignement adapté à leur niveau, favorisant la qualité de lecture, la maîtrise des règles de récitation et la progression individuelle."],
      'RÉSULTAT ATTENDU',
      ["Des séminaristes mieux orientés, plus compétents dans leur lecture du Saint Coran et capables de mesurer leur propre progression, grâce à un dispositif d'apprentissage adapté et évalué."]],
    ['1.', "IQRA' TIME", 'La bibliothèque vivante des JOSPIA',
      ["Promouvoir la culture de la lecture et stimuler la curiosité intellectuelle des séminaristes à travers une bibliothèque interactive dédiée à la découverte des ouvrages islamiques, littéraires, historiques et culturels."],
      'RÉSULTATS ATTENDUS',
      ["Une meilleure familiarisation des séminaristes avec les ouvrages disponibles ;", "Un intérêt renforcé pour la lecture et la recherche documentaire ;", "Une participation active à des animations éducatives ;", "Une dynamique de lecture appelée à se poursuivre au-delà des JOSPIA."]],
    ['', "JE ME GBACHI'S", 'Séance collective de fitness et de bien-être',
      ["Favoriser le bien-être physique, la cohésion entre séminaristes et l'adoption de bonnes habitudes de vie à travers des séances collectives de fitness alliant détente, divertissement et discipline personnelle."],
      'RÉSULTATS ATTENDUS',
      ["Des séminaristes ayant vécu un moment de détente et de divertissement collectif ;", "Une cohésion renforcée grâce à la participation et à l'effort partagé ;", "Une sensibilisation aux bonnes habitudes de vie et à la pratique du sport."]],
    ['', 'JOSPIA FACE-À-FACE', 'Le choc des idées, la force des arguments',
      ["Développer les capacités d'argumentation, d'analyse critique et d'expression des séminaristes à travers des débats contradictoires et constructifs."],
      'RÉSULTATS ATTENDUS',
      ["Renforcement des capacités d'argumentation et d'expression orale ;", "Développement de l'esprit critique et de l'écoute active ;", "Amélioration de la capacité à défendre et à confronter des idées avec respect ;", "Approfondissement des connaissances sur les thématiques abordées ;", "Participation accrue des séminaristes à leur propre formation."]],
    ['', 'PARCOURS INSPIRANT', '',
      ["Inspirer et édifier les séminaristes à travers des témoignages authentiques, afin de renforcer leur foi, leur persévérance, leur ambition et leur capacité à faire des choix éclairés."],
      'RÉSULTATS ATTENDUS',
      ["Des séminaristes inspirés par des expériences authentiques et édifiantes ;", "Une meilleure compréhension de l'importance de la persévérance, de la discipline et des choix ;", "Un renforcement de leur motivation dans leurs parcours personnel, académique et associatif ;", "Une appropriation d'enseignements concrets pouvant guider leurs décisions futures."]],
    ['6-', "UNE NUIT AVEC LE QUR'AN", "De la beauté de la récitation à l'adoration nocturne",
      ["Renforcer l'attachement des séminaristes au Saint Qur'an à travers une expérience alliant récitation, écoute et adoration."],
      'RÉSULTATS ATTENDUS',
      ["Un attachement accru au Saint Qur'an et à sa récitation ;", "La valorisation des talents coraniques des séminaristes ;", "Une participation renforcée à la prière nocturne ;", "Une expérience spirituelle favorisant le recueillement et la persévérance dans l'adoration."]],
    ['7-', 'DÉFI MÉMOIRE', 'Concept : Jouer, mémoriser, découvrir !',
      ["Stimuler la mémoire, l'observation et la concentration des séminaristes à travers une compétition éducative entre frères et sœurs."],
      'RÉSULTATS ATTENDUS',
      ["Renforcement de la mémoire, de l'observation et de la concentration ;", "Développement de la participation et de la coopération ;", "Consolidation de la cohésion et de l'émulation entre frères et sœurs."]],
  ]],
];
function pTxt($s) { return str_replace('15e édition', '15<sup>e</sup> édition', e($s)); }
$total = 0; foreach ($sections as $s) { $total += 1 + count($s[2]); }
function mots($s, $base = 0) { $o = ''; $i = $base; foreach (preg_split('/\s+/', trim($s)) as $m) { $o .= '<span class="mt" style="--w:' . $i++ . '">' . e($m) . '</span> '; } return $o; }
$total = 0; foreach ($sections as $s) { $total += 1 + count($s[2]); }
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Grandes activités et innovations — JOSPIA 2026</title>
<style>
:root{--v:#0B8A4E;--vf:#06532F;--o:#F77F00;--oc:#FFA43A;--r:#E8431E;--tx:#14301F}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;background:#fff;color:var(--tx);font-family:'Inter',system-ui,'Segoe UI',Arial,sans-serif}
/* fond blanc brillant et vivant */
.fond{position:fixed;inset:0;background:radial-gradient(ellipse at 15% 0,#fff4e4 0,transparent 50%),radial-gradient(ellipse at 100% 100%,#e6f7ee 0,transparent 55%),#fff;overflow:hidden}
.fond i{position:absolute;border-radius:50%;filter:blur(2px);animation:fl 12s ease-in-out infinite}
.fond i:nth-child(1){width:34vmin;height:34vmin;left:-8vmin;top:40%;background:radial-gradient(circle at 30% 30%,#ffd9a8,rgba(247,127,0,.18))}
.fond i:nth-child(2){width:26vmin;height:26vmin;right:-4vmin;top:22%;background:radial-gradient(circle at 30% 30%,#bff0d4,rgba(11,138,78,.18));animation-delay:-4s}
.fond i:nth-child(3){width:14vmin;height:14vmin;left:42%;bottom:4%;background:radial-gradient(circle at 30% 30%,#ffe2bd,rgba(247,127,0,.2));animation-delay:-8s}
.fond i:nth-child(4){width:9vmin;height:9vmin;left:12%;top:14%;background:radial-gradient(circle at 30% 30%,#c9f3dc,rgba(11,138,78,.22));animation-delay:-2s}
.fond i:nth-child(5){width:11vmin;height:11vmin;right:14%;bottom:14%;background:radial-gradient(circle at 30% 30%,#ffd9a8,rgba(232,67,30,.16));animation-delay:-6s}
@keyframes fl{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(2vmin,-4vmin) scale(1.1)}66%{transform:translate(-3vmin,-1vmin) scale(.94)}}
.brille{position:fixed;inset:0;pointer-events:none;background:linear-gradient(115deg,transparent 40%,rgba(255,255,255,.9) 50%,transparent 60%);background-size:250% 100%;animation:bril 7s ease-in-out infinite;opacity:.55;mix-blend-mode:overlay}
@keyframes bril{0%{background-position:120% 0}60%,100%{background-position:-80% 0}}
.barre{position:fixed;top:0;left:0;height:6px;background:linear-gradient(90deg,var(--v),var(--o),var(--r));width:0;transition:width .6s cubic-bezier(.4,0,.2,1);z-index:30;box-shadow:0 0 12px rgba(247,127,0,.6)}
/* entête du badge */
.haut{position:fixed;top:6px;left:0;right:0;z-index:20;display:flex;justify-content:center;padding:6px clamp(10px,3vw,30px) 0;animation:desc .9s cubic-bezier(.2,.8,.2,1) both}
.haut img{width:min(100%,940px);height:auto;display:block;mix-blend-mode:multiply;-webkit-mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent),linear-gradient(#000 85%,transparent);-webkit-mask-composite:source-in;mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent),linear-gradient(#000 85%,transparent);mask-composite:intersect}
@keyframes desc{from{opacity:0;transform:translateY(-40px)}}
.petit{position:fixed;right:clamp(10px,2.4vw,30px);bottom:78px;width:clamp(54px,7vw,92px);z-index:12;animation:flo 4s ease-in-out infinite;mix-blend-mode:multiply}
@keyframes flo{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-12px) rotate(3deg)}}
/* scène */
.slide{position:absolute;left:0;right:0;top:calc(min(100vw - 20px,940px)*.128 + 26px);bottom:84px;display:flex;opacity:0;visibility:hidden;transform:translateX(80px) scale(.97);transition:opacity .7s,transform .8s cubic-bezier(.2,.8,.2,1),visibility 0s .8s;overflow-y:auto;overflow-x:hidden;padding:0 clamp(16px,6vw,90px)}
.slide.actif{opacity:1;visibility:visible;transform:none;transition:opacity .7s,transform .8s cubic-bezier(.2,.8,.2,1)}
.slide.avant{transform:translateX(-80px) scale(.97)}
.contenu{margin:auto;width:100%;max-width:1200px;padding:8px 0}
.el{opacity:0;transform:translateY(34px)}
.actif .el{animation:ap .9s cubic-bezier(.2,.8,.2,1) forwards;animation-delay:calc(var(--i,0)*.18s + .2s)}
@keyframes ap{to{opacity:1;transform:none}}
/* mots qui bougent */
.mt{display:inline-block;opacity:0;transform:translateY(40px) rotate(4deg);filter:blur(8px)}
.actif .mt{animation:mot .8s cubic-bezier(.2,.9,.3,1.3) forwards,ond 4s ease-in-out infinite;animation-delay:calc(var(--w)*.09s + .3s),calc(var(--w)*.09s + 1.3s)}
@keyframes mot{to{opacity:1;transform:none;filter:none}}
@keyframes ond{0%,100%{translate:0 0}50%{translate:0 -4px}}
.degrade .mt{background:linear-gradient(90deg,var(--v),var(--o),var(--r),var(--o),var(--v));background-size:300% 100%;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent}
.actif .degrade .mt{animation:mot .8s cubic-bezier(.2,.9,.3,1.3) forwards,ond 4s ease-in-out infinite,coul 6s linear infinite;animation-delay:calc(var(--w)*.09s + .3s),calc(var(--w)*.09s + 1.3s),0s}
@keyframes coul{to{background-position:300% 0}}
/* parties */
.partie{text-align:center}
.partie .logo{width:clamp(150px,30vh,300px);animation:flo 5s ease-in-out infinite;mix-blend-mode:multiply}
.partie .num{display:block;font-size:clamp(60px,13vw,150px);font-weight:900;line-height:1;margin-top:4px}
.partie h1{font-size:clamp(26px,5.6vw,68px);font-weight:900;letter-spacing:.03em;line-height:1.1}
.trait{height:7px;width:0;background:linear-gradient(90deg,var(--v),var(--o),var(--r));border-radius:7px;margin:22px auto 0}
.actif .trait{animation:tr 1.2s 1s ease forwards}
@keyframes tr{to{width:min(300px,55%)}}
/* fiches */
.entete{margin-bottom:clamp(12px,2vw,24px)}
.entete .n{display:inline-block;background:linear-gradient(135deg,var(--o),var(--r));color:#fff;font-weight:800;border-radius:14px;padding:5px 16px;font-size:clamp(16px,2.2vw,26px);margin-bottom:10px;box-shadow:0 8px 18px rgba(247,127,0,.35);animation:puls 2.6s ease-in-out infinite}
@keyframes puls{50%{transform:scale(1.07)}}
.entete h2{font-size:clamp(22px,4vw,50px);font-weight:900;line-height:1.12;text-transform:uppercase}
.entete p{font-style:italic;color:var(--v);font-weight:600;font-size:clamp(15px,1.9vw,23px);margin-top:8px}
.cols{display:grid;grid-template-columns:1fr 1fr;gap:clamp(12px,2vw,26px)}
.bloc{position:relative;background:#fff;border:1px solid #e3efe8;border-radius:22px;padding:clamp(16px,2.2vw,30px);box-shadow:0 14px 34px rgba(11,138,78,.12);overflow:hidden}
.bloc:before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:linear-gradient(var(--v),var(--o))}
.bloc:after{content:"";position:absolute;top:0;left:-60%;width:40%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.95),transparent);transform:skewX(-18deg);animation:reflet 5s ease-in-out infinite}
@keyframes reflet{0%{left:-60%}45%,100%{left:140%}}
.bloc h3{font-size:clamp(12px,1.3vw,15px);letter-spacing:.2em;color:var(--o);margin-bottom:14px;font-weight:800}
.bloc .obj{font-size:clamp(15px,1.7vw,21px);line-height:1.55}
.bloc ul{list-style:none;display:grid;gap:clamp(8px,1.2vw,14px)}
.bloc li{position:relative;padding-left:28px;font-size:clamp(14px,1.55vw,19px);line-height:1.45;opacity:0;transform:translateX(40px)}
.bloc li:before{content:"";position:absolute;left:0;top:.45em;width:13px;height:13px;border-radius:50%;background:linear-gradient(135deg,var(--o),var(--r));box-shadow:0 0 0 4px rgba(247,127,0,.2);animation:bat 2s ease-in-out infinite}
@keyframes bat{50%{box-shadow:0 0 0 9px rgba(247,127,0,0);transform:scale(1.15)}}
.actif .bloc li{animation:ap2 .8s cubic-bezier(.2,.8,.2,1) forwards;animation-delay:calc(var(--k)*.3s + 1.1s)}
@keyframes ap2{to{opacity:1;transform:none}}
/* bas */
.bas{position:fixed;left:0;right:0;bottom:0;display:flex;justify-content:space-between;align-items:center;padding:12px clamp(14px,3vw,36px) calc(12px + env(safe-area-inset-bottom));z-index:25;gap:10px;background:linear-gradient(transparent,rgba(255,255,255,.9) 40%)}
.nav{display:flex;gap:10px;align-items:center}
.nav button,.nav a{width:50px;height:50px;border-radius:50%;border:none;background:linear-gradient(135deg,var(--o),var(--r));color:#fff;font-size:22px;cursor:pointer;box-shadow:0 8px 20px rgba(232,67,30,.35);transition:.2s;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-family:inherit}
.nav button:hover,.nav a:hover{transform:scale(1.12)}
.nav button:disabled{opacity:.3;pointer-events:none}
.nav .sec{background:#fff;color:var(--vf);border:2px solid var(--v);box-shadow:0 6px 14px rgba(11,138,78,.2);font-size:16px;font-weight:700}
.points{display:flex;gap:6px;flex-wrap:wrap;justify-content:center;max-width:50vw}
.points span{width:10px;height:10px;border-radius:50%;background:#cfe3d7;cursor:pointer;transition:.3s}
.points span.on{background:var(--o);transform:scale(1.6)}
.cpt{font-weight:800;font-size:14px;color:var(--vf);min-width:56px;text-align:right}
@media(max-width:820px){.cols{grid-template-columns:1fr}.slide{align-items:flex-start}.points{display:none}.petit{display:none}}
@media(max-height:520px) and (orientation:landscape){.slide{bottom:66px}.nav button,.nav a{width:42px;height:42px}.petit{display:none}.partie .logo{width:90px}}
@media(prefers-reduced-motion:reduce){*{animation-duration:.01s!important;animation-delay:0s!important;transition-duration:.01s!important}}
</style>
</head>
<body>
<div class="fond"><i></i><i></i><i></i><i></i><i></i></div>
<div class="brille"></div>
<div class="barre" id="barre"></div>
<div class="haut"><img src="<?= BASE_URL ?>/assets/img/pres_bandeau.png" alt="AEEMCI — Secrétariat Régional Abidjan Nord — Sous-comités Anyama 1 &amp; 2 — JOSPIA"></div>
<div id="scene">
<?php foreach ($sections as [$rom, $titre, $items]): ?>
  <section class="slide"><div class="contenu partie">
    <div class="el" style="--i:0"><img class="logo" src="<?= BASE_URL ?>/assets/img/pres_logo.png" alt="JOSPIA 2026"></div>
    <span class="num degrade"><?= mots($rom) ?></span>
    <h1 class="degrade"><?= mots($titre, 2) ?></h1>
    <div class="trait"></div>
  </div></section>
  <?php foreach ($items as [$num, $nom, $sous, $obj, $labelR, $res]): ?>
  <section class="slide"><div class="contenu">
    <div class="entete">
      <?php if ($num !== ''): ?><span class="n el" style="--i:0"><?= e($num) ?></span><?php endif; ?>
      <h2 class="degrade"><?= mots($nom) ?></h2>
      <?php if ($sous !== ''): ?><p class="el" style="--i:3">(<?= e($sous) ?>)</p><?php endif; ?>
    </div>
    <div class="cols">
      <div class="bloc el" style="--i:2"><h3>OBJECTIF GÉNÉRAL :</h3><p class="obj"><?= pTxt($obj[0]) ?></p></div>
      <div class="bloc el" style="--i:3"><h3><?= e($labelR) ?> :</h3><ul>
        <?php foreach ($res as $k => $r): ?><li style="--k:<?= $k ?>"><?= e($r) ?></li><?php endforeach; ?>
      </ul></div>
    </div>
  </div></section>
  <?php endforeach; ?>
<?php endforeach; ?>
</div>
<div class="bas">
  <div class="nav"><button id="prec" type="button" aria-label="Précédent">&#8592;</button><button id="suiv" type="button" aria-label="Suivant">&#8594;</button></div>
  <div class="points" id="points"></div>
  <div class="nav"><a class="sec" href="<?= BASE_URL ?>/admin/comite_manageriale" title="Quitter" aria-label="Quitter">&#10005;</a><button class="sec" id="plein" type="button" title="Plein écran" aria-label="Plein écran">&#x26F6;</button><div class="cpt" id="cpt"></div></div>
</div>
<script>
(function(){
  var sl=[].slice.call(document.querySelectorAll('.slide')),n=sl.length,c=0;
  var pts=document.getElementById('points'),cpt=document.getElementById('cpt'),bar=document.getElementById('barre');
  sl.forEach(function(_,i){var s=document.createElement('span');s.onclick=function(){go(i)};pts.appendChild(s)});
  function go(i){
    if(i<0||i>=n)return;
    sl.forEach(function(s,k){s.classList.toggle('actif',k===i);s.classList.toggle('avant',k<i);if(k===i)s.scrollTop=0});
    c=i;
    [].forEach.call(pts.children,function(p,k){p.classList.toggle('on',k===i)});
    cpt.textContent=(i+1)+' / '+n;
    bar.style.width=((i+1)/n*100)+'%';
    document.getElementById('prec').disabled=i===0;
    document.getElementById('suiv').disabled=i===n-1;
    try{history.replaceState(null,'','#'+(i+1))}catch(e){}
  }
  document.getElementById('prec').onclick=function(){go(c-1)};
  document.getElementById('suiv').onclick=function(){go(c+1)};
  document.addEventListener('keydown',function(e){
    if(['ArrowRight','PageDown',' ','Enter'].indexOf(e.key)>=0){e.preventDefault();go(c+1)}
    else if(['ArrowLeft','PageUp','Backspace'].indexOf(e.key)>=0){e.preventDefault();go(c-1)}
    else if(e.key==='Home')go(0);else if(e.key==='End')go(n-1);
    else if(e.key==='f'||e.key==='F')pl();
  });
  var x0=null,y0=null;
  document.addEventListener('touchstart',function(e){x0=e.touches[0].clientX;y0=e.touches[0].clientY},{passive:true});
  document.addEventListener('touchend',function(e){
    if(x0===null)return;var dx=e.changedTouches[0].clientX-x0,dy=e.changedTouches[0].clientY-y0;x0=null;
    if(Math.abs(dx)>60&&Math.abs(dx)>Math.abs(dy)*1.5)go(dx<0?c+1:c-1);
  },{passive:true});
  function pl(){var d=document.documentElement;
    if(!document.fullscreenElement){(d.requestFullscreen||d.webkitRequestFullscreen||function(){}).call(d)}
    else{(document.exitFullscreen||document.webkitExitFullscreen).call(document)}}
  document.getElementById('plein').onclick=pl;
  var h=parseInt((location.hash||'').slice(1),10);
  go(h>=1&&h<=n?h-1:0);
})();
</script>
</body>
</html>
