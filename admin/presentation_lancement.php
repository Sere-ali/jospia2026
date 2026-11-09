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
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Grandes activités et innovations — JOSPIA 2026</title>
<style>
:root{--v:#0B8A4E;--vf:#06532F;--vn:#032d1a;--o:#F77F00;--oc:#FF9D33;--t:#fff}
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden;background:var(--vn);color:var(--t);font-family:'Inter',system-ui,Segoe UI,Arial,sans-serif}
.fond{position:fixed;inset:0;background:radial-gradient(ellipse at 20% 10%,#0e6b3d 0,transparent 55%),radial-gradient(ellipse at 90% 100%,#8a4300 0,transparent 45%),linear-gradient(160deg,#032d1a,#06532F 60%,#032d1a)}
.fond i{position:absolute;border-radius:50%;background:rgba(247,127,0,.13);animation:fl 14s ease-in-out infinite}
.fond i:nth-child(1){width:38vmin;height:38vmin;left:-8vmin;top:55%}
.fond i:nth-child(2){width:24vmin;height:24vmin;right:6%;top:8%;animation-delay:-5s;background:rgba(255,255,255,.06)}
.fond i:nth-child(3){width:14vmin;height:14vmin;left:45%;bottom:6%;animation-delay:-9s}
@keyframes fl{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4vmin) scale(1.12)}}
.barre{position:fixed;top:0;left:0;height:5px;background:linear-gradient(90deg,var(--o),var(--oc));width:0;transition:width .6s cubic-bezier(.4,0,.2,1);z-index:20}
.haut{position:fixed;top:0;left:0;right:0;display:flex;justify-content:space-between;align-items:center;padding:14px clamp(14px,3vw,36px);z-index:15}
.marque{font-weight:800;letter-spacing:.14em;font-size:clamp(12px,1.5vw,16px);display:flex;align-items:center;gap:10px}
.marque b{color:var(--oc)}
.marque img{height:clamp(26px,4vw,40px);border-radius:8px;background:#fff;padding:2px}
.outils{display:flex;gap:8px}
.outils a,.outils button{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);border-radius:999px;padding:8px 14px;font:600 13px inherit;font-family:inherit;cursor:pointer;text-decoration:none;backdrop-filter:blur(6px);transition:.2s}
.outils a:hover,.outils button:hover{background:var(--o);border-color:var(--o)}
.scene{position:fixed;inset:0;padding:70px 0 84px}
.slide{position:absolute;inset:70px 0 84px;display:flex;opacity:0;visibility:hidden;transform:translateX(60px) scale(.98);transition:opacity .7s,transform .7s cubic-bezier(.2,.8,.2,1),visibility 0s .7s;overflow-y:auto;padding:0 clamp(16px,6vw,90px)}
.slide.actif{opacity:1;visibility:visible;transform:none;transition:opacity .7s,transform .7s cubic-bezier(.2,.8,.2,1)}
.slide.avant{transform:translateX(-60px) scale(.98)}
.contenu{margin:auto;width:100%;max-width:1200px;padding:8px 0}
.el{opacity:0;transform:translateY(26px)}
.actif .el{animation:ap .8s cubic-bezier(.2,.8,.2,1) forwards;animation-delay:calc(var(--i,0)*.16s + .25s)}
@keyframes ap{to{opacity:1;transform:none}}
/* titre de partie */
.partie{text-align:center}
.partie .num{font-size:clamp(70px,16vw,200px);font-weight:900;line-height:1;background:linear-gradient(135deg,var(--o),var(--oc));-webkit-background-clip:text;background-clip:text;color:transparent}
.partie h1{font-size:clamp(28px,6.2vw,76px);font-weight:900;letter-spacing:.03em;line-height:1.1;margin-top:6px}
.trait{height:6px;width:0;background:linear-gradient(90deg,var(--o),var(--oc));border-radius:6px;margin:26px auto 0}
.actif .trait{animation:tr 1.1s .9s ease forwards}
@keyframes tr{to{width:min(260px,50%)}}
/* fiche activité */
.entete{margin-bottom:clamp(14px,2.4vw,28px)}
.entete .n{display:inline-block;background:var(--o);color:#fff;font-weight:800;border-radius:12px;padding:4px 14px;font-size:clamp(16px,2.4vw,28px);margin-bottom:10px}
.entete h2{font-size:clamp(24px,4.4vw,54px);font-weight:900;line-height:1.1;text-transform:uppercase}
.entete p{font-style:italic;color:var(--oc);font-size:clamp(15px,2vw,24px);margin-top:8px}
.cols{display:grid;grid-template-columns:1fr 1fr;gap:clamp(12px,2vw,26px)}
.bloc{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.18);border-radius:20px;padding:clamp(16px,2.4vw,30px);backdrop-filter:blur(8px)}
.bloc h3{font-size:clamp(12px,1.3vw,15px);letter-spacing:.2em;color:var(--oc);margin-bottom:14px;font-weight:800}
.bloc .obj{font-size:clamp(15px,1.75vw,22px);line-height:1.55}
.bloc ul{list-style:none;display:grid;gap:clamp(8px,1.2vw,14px)}
.bloc li{position:relative;padding-left:26px;font-size:clamp(14px,1.6vw,20px);line-height:1.45;opacity:0;transform:translateX(24px)}
.bloc li:before{content:"";position:absolute;left:0;top:.5em;width:12px;height:12px;border-radius:50%;background:var(--o);box-shadow:0 0 0 4px rgba(247,127,0,.25)}
.actif .bloc li{animation:ap2 .7s cubic-bezier(.2,.8,.2,1) forwards;animation-delay:calc(var(--k)*.28s + 1s)}
@keyframes ap2{to{opacity:1;transform:none}}
.bas{position:fixed;left:0;right:0;bottom:0;display:flex;justify-content:space-between;align-items:center;padding:14px clamp(14px,3vw,36px) calc(14px + env(safe-area-inset-bottom));z-index:15;gap:10px}
.nav{display:flex;gap:10px}
.nav button{width:50px;height:50px;border-radius:50%;border:none;background:var(--o);color:#fff;font-size:22px;cursor:pointer;box-shadow:0 6px 18px rgba(0,0,0,.35);transition:.2s}
.nav button:hover{transform:scale(1.1);background:var(--oc)}
.nav button:disabled{opacity:.3;pointer-events:none}
.points{display:flex;gap:6px;flex-wrap:wrap;justify-content:center;max-width:60vw}
.points span{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.3);cursor:pointer;transition:.3s}
.points span.on{background:var(--o);transform:scale(1.5)}
.cpt{font-weight:700;font-size:14px;opacity:.85;min-width:56px;text-align:right}
@media(max-width:820px){.cols{grid-template-columns:1fr}.slide{align-items:flex-start}.points{display:none}.cpt{order:3}.bas{justify-content:space-between}.outils a span{display:none}}
@media(max-height:520px) and (orientation:landscape){.haut{padding-top:6px}.scene,.slide{}.slide{inset:52px 0 66px}.nav button{width:42px;height:42px}}
@media(prefers-reduced-motion:reduce){*{animation-duration:.01s!important;animation-delay:0s!important;transition-duration:.01s!important}}
</style>
</head>
<body>
<div class="fond"><i></i><i></i><i></i></div>
<div class="barre" id="barre"></div>
<div class="haut">
  <div class="marque"><img src="<?= BASE_URL ?>/assets/img/logo.jpg" alt=""><span>JOSPIA <b>2026</b></span></div>
  <div class="outils"><a href="<?= BASE_URL ?>/admin/comite_manageriale">&larr; <span>Quitter</span></a><button id="plein" type="button">Plein écran</button></div>
</div>
<div class="scene" id="scene">
<?php foreach ($sections as [$rom, $titre, $items]): ?>
  <section class="slide"><div class="contenu partie">
    <div class="num el" style="--i:0"><?= e($rom) ?></div>
    <h1 class="el" style="--i:1"><?= e($titre) ?></h1>
    <div class="trait"></div>
  </div></section>
  <?php foreach ($items as [$num, $nom, $sous, $obj, $labelR, $res]): ?>
  <section class="slide"><div class="contenu">
    <div class="entete el" style="--i:0">
      <?php if ($num !== ''): ?><span class="n"><?= e($num) ?></span><?php endif; ?>
      <h2><?= e($nom) ?></h2>
      <?php if ($sous !== ''): ?><p>(<?= e($sous) ?>)</p><?php endif; ?>
    </div>
    <div class="cols">
      <div class="bloc el" style="--i:1"><h3>OBJECTIF GÉNÉRAL :</h3><p class="obj"><?= pTxt($obj[0]) ?></p></div>
      <div class="bloc el" style="--i:2"><h3><?= e($labelR) ?> :</h3><ul>
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
  <div class="cpt" id="cpt"></div>
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
