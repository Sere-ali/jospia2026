<?php
require_once __DIR__ . '/../includes/init.php';
if (!defined('PRESENTATION_PUBLIQUE')) { exigerRole(['admin', 'superadmin', 'mg']); }
require_once __DIR__ . '/../includes/responsables_pres.php';
$responsables = respPresListe($pdo);
function mots($s, $base = 0) { $o = ''; $i = $base; foreach (preg_split('/\s+/', trim($s)) as $m) { $t = ($m === '15e') ? '15<sup>e</sup>' : e($m); $o .= '<span class="w"><i style="--d:' . $i++ . '">' . $t . '</i></span> '; } return $o; }

?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Les responsables de commission — JOSPIA 2026</title>
<style>
:root{--vh:1vh;--v:#0B8A4E;--vf:#06532F;--o:#F77F00;--oc:#FFA43A;--r:#E8431E;--tx:#14301F;--hw:min(100vw - 20px,940px,calc(var(--vh)*125));--haut:calc(var(--hw)*.128 + 24px)}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;max-width:100%;overflow:hidden;overscroll-behavior:none;touch-action:manipulation;background:#fff;color:var(--tx);font-family:'Inter',system-ui,'Segoe UI',Arial,sans-serif;-webkit-tap-highlight-color:transparent}
.fond{position:fixed;inset:0;background:radial-gradient(ellipse at 15% 0,#fff4e4 0,transparent 50%),radial-gradient(ellipse at 100% 100%,#e6f7ee 0,transparent 55%),#fff;overflow:hidden}
.brille{position:fixed;inset:0;pointer-events:none;background:linear-gradient(115deg,transparent 40%,rgba(255,255,255,.9) 50%,transparent 60%);background-size:250% 100%;animation:bril 7s ease-in-out infinite;opacity:.55;mix-blend-mode:overlay;z-index:4}
@keyframes bril{0%{background-position:120% 0}60%,100%{background-position:-80% 0}}
.barre{position:fixed;top:0;left:0;height:6px;background:linear-gradient(90deg,var(--v),var(--o),var(--r));width:0;transition:width .6s cubic-bezier(.4,0,.2,1);z-index:30;box-shadow:0 0 12px rgba(247,127,0,.6)}
.haut{position:fixed;top:6px;left:0;right:0;max-width:100vw;overflow:hidden;z-index:20;display:flex;justify-content:center;padding:6px clamp(10px,3vw,30px) 0;animation:desc .9s cubic-bezier(.2,.8,.2,1) both}
.haut img{width:var(--hw);height:auto;display:block;mix-blend-mode:multiply;-webkit-mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent),linear-gradient(#000 85%,transparent);-webkit-mask-composite:source-in;mask-image:linear-gradient(90deg,transparent,#000 4%,#000 96%,transparent),linear-gradient(#000 85%,transparent);mask-composite:intersect}
@keyframes desc{from{opacity:0;transform:translateY(-40px)}}
@keyframes flo{0%,100%{transform:translateY(0) rotate(-3deg)}50%{transform:translateY(-12px) rotate(3deg)}}
/* ---------- transitions (6 modèles, comme la présentation Kirina) ---------- */
#wipe{position:fixed;inset:0;z-index:22;pointer-events:none;overflow:hidden}
#wipe b{position:absolute;display:none;will-change:transform}
#wipe.go b{display:block}
#wipe.t0 b{top:-15%;bottom:-15%;left:-80%;width:75%;transform:skewX(-16deg);animation:wp 1.15s cubic-bezier(.75,0,.25,1) both}
#wipe.t0 b:nth-child(1){background:linear-gradient(90deg,var(--o),var(--oc))}
#wipe.t0 b:nth-child(2){background:linear-gradient(90deg,var(--v),#12A862);animation-delay:.09s}
#wipe.t0 b:nth-child(3){background:var(--vf);animation-delay:.18s}
#wipe.t0 b:nth-child(n+4){display:none}
@keyframes wp{from{transform:skewX(-16deg) translateX(0)}to{transform:skewX(-16deg) translateX(330%)}}
#wipe.t1 b{top:0;height:100%;width:12.6%;animation:vv 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t1 b:nth-child(odd){background:var(--vf)}#wipe.t1 b:nth-child(even){background:linear-gradient(180deg,var(--o),var(--oc))}
#wipe.t1 b:nth-child(1){left:0}#wipe.t1 b:nth-child(2){left:12.5%;animation-delay:.05s}#wipe.t1 b:nth-child(3){left:25%;animation-delay:.1s}#wipe.t1 b:nth-child(4){left:37.5%;animation-delay:.15s}
#wipe.t1 b:nth-child(5){left:50%;animation-delay:.2s}#wipe.t1 b:nth-child(6){left:62.5%;animation-delay:.25s}#wipe.t1 b:nth-child(7){left:75%;animation-delay:.3s}#wipe.t1 b:nth-child(8){left:87.5%;animation-delay:.35s}
@keyframes vv{0%{transform:translateY(-101%)}40%,55%{transform:translateY(0)}100%{transform:translateY(101%)}}
#wipe.t2 b{left:0;width:100%;height:12.6%;animation:hh 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t2 b:nth-child(odd){background:var(--v)}#wipe.t2 b:nth-child(even){background:var(--vf)}
#wipe.t2 b:nth-child(1){top:0}#wipe.t2 b:nth-child(2){top:12.5%;animation-delay:.05s}#wipe.t2 b:nth-child(3){top:25%;animation-delay:.1s}#wipe.t2 b:nth-child(4){top:37.5%;animation-delay:.15s}
#wipe.t2 b:nth-child(5){top:50%;animation-delay:.2s}#wipe.t2 b:nth-child(6){top:62.5%;animation-delay:.25s}#wipe.t2 b:nth-child(7){top:75%;animation-delay:.3s}#wipe.t2 b:nth-child(8){top:87.5%;animation-delay:.35s}
@keyframes hh{0%{transform:translateX(-101%)}40%,55%{transform:translateX(0)}100%{transform:translateX(101%)}}
#wipe.t3 b:nth-child(1){inset:0;background:radial-gradient(circle,var(--v) 0,var(--vf) 70%);animation:ir 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t3 b:nth-child(2){left:50%;top:50%;width:140vmax;height:140vmax;margin:-70vmax 0 0 -70vmax;border-radius:50%;border:14px solid var(--o);animation:ir2 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t3 b:nth-child(n+3){display:none}
@keyframes ir{0%{clip-path:circle(0% at 50% 50%)}45%,60%{clip-path:circle(75% at 50% 50%)}100%{clip-path:circle(75% at 50% 50%);opacity:0}}
@keyframes ir2{0%{transform:scale(0);opacity:1}45%{transform:scale(1);opacity:1}60%,100%{transform:scale(1.15);opacity:0}}
#wipe.t4 b:nth-child(1){inset:0;background:radial-gradient(circle,#fff 0,#ffd9a8 30%,var(--o) 70%);animation:flsh 1s ease-in-out both}
#wipe.t4 b:nth-child(2){inset:0;background:var(--vf);animation:flsh2 1s ease-in-out both}
#wipe.t4 b:nth-child(n+3){display:none}
@keyframes flsh{0%{opacity:0;transform:scale(.6)}45%{opacity:1;transform:scale(1.2)}100%{opacity:0;transform:scale(1.7)}}
@keyframes flsh2{0%{opacity:0}40%,55%{opacity:.9}100%{opacity:0}}
#wipe.t5 b{top:-60%;left:-10%;width:130%;height:34%;transform:rotate(-20deg) translateX(-130%);animation:dg 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t5 b:nth-child(odd){background:var(--vf)}#wipe.t5 b:nth-child(even){background:linear-gradient(90deg,var(--o),var(--oc))}
#wipe.t5 b:nth-child(1){top:-30%}#wipe.t5 b:nth-child(2){top:-10%;animation-delay:.06s}#wipe.t5 b:nth-child(3){top:10%;animation-delay:.12s}#wipe.t5 b:nth-child(4){top:30%;animation-delay:.18s}
#wipe.t5 b:nth-child(5){top:50%;animation-delay:.24s}#wipe.t5 b:nth-child(6){top:70%;animation-delay:.3s}#wipe.t5 b:nth-child(7){top:90%;animation-delay:.36s}#wipe.t5 b:nth-child(8){display:none}
@keyframes dg{0%{transform:rotate(-20deg) translateX(-130%)}40%,55%{transform:rotate(-20deg) translateX(0)}100%{transform:rotate(-20deg) translateX(130%)}}
#scene{position:fixed;inset:0;overflow:hidden;contain:paint;z-index:5}
/* diapositives : la bascule se fait sous le rideau, puis l'entrée est animée */
.slide{position:absolute;left:0;right:0;top:var(--haut);bottom:84px;max-width:100vw;display:flex;opacity:0;visibility:hidden;z-index:5;overflow-y:auto;overflow-x:hidden;padding:0 clamp(16px,6vw,90px)}
.slide.actif{opacity:1;visibility:visible;z-index:6}
.slide.actif.e1 .contenu{animation:e1 1s cubic-bezier(.16,.9,.25,1) .45s both}
.slide.actif.e2 .contenu{animation:e2 1s cubic-bezier(.16,.9,.25,1) .45s both}
.slide.actif.e3 .contenu{animation:e3 1s cubic-bezier(.16,.9,.25,1) .45s both}
@keyframes e1{from{opacity:0;transform:scale(1.14);filter:blur(10px)}to{opacity:1;transform:none;filter:none}}
@keyframes e2{from{opacity:0;transform:perspective(1600px) rotateX(16deg) translateY(50px)}to{opacity:1;transform:none}}
@keyframes e3{from{opacity:0;transform:translateX(120px)}to{opacity:1;transform:none}}
.contenu{margin:auto;width:100%;max-width:min(100%,1800px);padding:8px 0}
.el{opacity:0}
.actif .el{animation:ap 1s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.6s + var(--i,0)*.12s)}
@keyframes ap{from{opacity:0;transform:translateY(70px) scale(.96)}to{opacity:1;transform:none}}
.w{display:inline-block;overflow:hidden;vertical-align:top;padding:.2em .05em .1em;margin:-.2em -.05em -.1em}
.w i{display:inline-block;font-style:normal;transform:translateY(118%)}
.actif .w i{animation:mot 1s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.5s + var(--d)*.11s)}
@keyframes mot{from{transform:translateY(118%) rotate(5deg)}to{transform:none}}
.degrade .w i{background:linear-gradient(90deg,var(--v),var(--o),var(--r),var(--o),var(--v));background-size:300% 100%;-webkit-background-clip:text;background-clip:text;color:transparent;-webkit-text-fill-color:transparent}
.actif .degrade .w i{animation:mot 1s cubic-bezier(.16,.95,.25,1) both,coul 6s linear infinite;animation-delay:calc(.5s + var(--d)*.11s),0s}
@keyframes coul{to{background-position:300% 0}}
.partie{text-align:center}
.partie .logo{width:clamp(90px,calc(var(--vh)*26),300px);animation:flo 5s ease-in-out infinite}
.partie .num{display:block;font-size:clamp(50px,min(13vw,calc(var(--vh)*20)),150px);font-weight:900;line-height:1;margin-top:4px}
.partie h1{font-size:clamp(24px,min(5.6vw,calc(var(--vh)*10)),68px);font-weight:900;letter-spacing:.03em;line-height:1.1}
.trait{height:7px;width:0;background:linear-gradient(90deg,var(--v),var(--o),var(--r));border-radius:7px;margin:22px auto 0}
.actif .trait{animation:tr 1.2s 1.6s ease forwards}
@keyframes tr{to{width:min(300px,55%)}}
.entete{margin-bottom:clamp(12px,2vw,24px)}
.entete .n{display:inline-block;background:linear-gradient(135deg,var(--o),var(--r));color:#fff;font-weight:800;border-radius:14px;padding:5px 16px;font-size:clamp(17px,min(2.6vw,calc(var(--vh)*4.4)),44px);margin-bottom:10px;box-shadow:0 8px 18px rgba(247,127,0,.35)}
.actif .entete .n{animation:ap 1s cubic-bezier(.16,.95,.25,1) both,puls 2.6s 2s ease-in-out infinite;animation-delay:.6s,2s}
@keyframes puls{50%{transform:scale(1.07)}}
.entete h2{font-size:clamp(22px,min(4.8vw,calc(var(--vh)*8.5)),84px);font-weight:900;line-height:1.12;text-transform:uppercase}
.entete p{font-style:italic;color:var(--v);font-weight:700;font-size:clamp(15px,min(2.4vw,calc(var(--vh)*4.2)),40px);margin-top:8px}
/* deux temps : objectif d'abord, résultats ensuite */
.cols{display:grid;grid-template-columns:1fr 0fr;column-gap:0;transition:grid-template-columns .9s cubic-bezier(.2,.8,.2,1),column-gap .9s}
.cols.s2{grid-template-columns:1fr 1fr;column-gap:clamp(12px,2vw,26px)}
.bloc{position:relative;background:#fff;border:1px solid #e3efe8;border-radius:22px;padding:clamp(12px,min(2.2vw,calc(var(--vh)*3.5)),30px);box-shadow:0 14px 34px rgba(11,138,78,.12);overflow:hidden;min-width:0;transition:transform .25s,box-shadow .25s}
.bloc:before{content:"";position:absolute;left:0;top:0;bottom:0;width:6px;background:linear-gradient(var(--v),var(--o))}
.bloc:after{content:"";position:absolute;top:0;left:-60%;width:40%;height:100%;background:linear-gradient(100deg,transparent,rgba(255,255,255,.95),transparent);transform:skewX(-18deg);animation:reflet 5s ease-in-out infinite}
@keyframes reflet{0%{left:-60%}45%,100%{left:140%}}
.bloc:hover{box-shadow:0 22px 44px rgba(247,127,0,.22)}
.bloc h3{font-size:clamp(15px,min(2.5vw,calc(var(--vh)*4.4)),46px);letter-spacing:.16em;color:var(--o);margin-bottom:.7em;font-weight:900}
.bloc .obj{font-size:clamp(18px,min(3.3vw,calc(var(--vh)*6)),64px);line-height:1.4;font-weight:600}
.objb{opacity:0;transform:translateY(60px) scale(.96);transition:opacity .9s,transform 1s cubic-bezier(.16,.95,.25,1),box-shadow .25s}
.s1 .objb{opacity:1;transform:none}
.actif .obj .w i{animation:none}
.actif .s1 .obj .w i{animation:mot 1s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.25s + var(--d)*.05s)}
.res{opacity:0;padding-left:0;padding-right:0;border-width:0;transform:translateX(60px);transition:opacity .7s .3s,padding .9s,transform .9s .2s cubic-bezier(.2,.8,.2,1)}
.cols:not(.s2) .res{height:0;min-height:0;padding-top:0;padding-bottom:0;overflow:hidden}
.s2 .res{opacity:1;padding-left:clamp(12px,min(2.2vw,calc(var(--vh)*3.5)),30px);padding-right:clamp(12px,min(2.2vw,calc(var(--vh)*3.5)),30px);border-width:1px;transform:none}
.bloc ul{list-style:none;display:grid;gap:clamp(10px,min(1.8vw,calc(var(--vh)*2.6)),30px)}
.bloc li{position:relative;padding-left:1.5em;font-size:clamp(17px,min(3vw,calc(var(--vh)*5.4)),58px);font-weight:600;line-height:1.35;opacity:0;transform:translateX(40px) scale(.96)}
.bloc li:before{content:"";position:absolute;left:0;top:.42em;width:.55em;height:.55em;border-radius:50%;background:linear-gradient(135deg,var(--o),var(--r));box-shadow:0 0 0 4px rgba(247,127,0,.2);animation:bat 2s ease-in-out infinite}
@keyframes bat{50%{box-shadow:0 0 0 9px rgba(247,127,0,0);transform:scale(1.15)}}
.bloc li.on{animation:ap2 .9s cubic-bezier(.16,.95,.25,1) forwards}
@keyframes ap2{to{opacity:1;transform:none}}
.cols.solo,.cols.solo.s2{grid-template-columns:1fr;column-gap:0}
.cols.solo .objb{display:none}
.fit .cols.solo{grid-template-columns:1fr!important;column-gap:0!important}



/* ===== Décor : cercles orange et verts qui tournent ===== */
.par{position:absolute;inset:0;transform:translate(calc(var(--mx,0)*var(--f,20)*1px),calc(var(--my,0)*var(--f,20)*1px));transition:transform .5s ease-out}
.p1{--f:-26}.p2{--f:30}.p3{--f:-14}.p4{--f:46}
.cer{position:absolute;border-radius:50%;transition:translate 1.6s cubic-bezier(.2,.8,.2,1)}
.cer:after{content:"";position:absolute;border-radius:50%;left:50%;top:0;width:var(--d,22px);height:var(--d,22px);margin:calc(var(--d,22px)/-2) 0 0 calc(var(--d,22px)/-2);background:var(--dc,var(--o));box-shadow:0 0 18px 3px var(--dc,var(--o))}
.r1{left:-15vmax;top:-17vmax;width:50vmax;height:50vmax;border:3px solid rgba(247,127,0,.55);background:radial-gradient(circle,rgba(247,127,0,.10),transparent 70%);animation:spin 80s linear infinite;translate:calc(sin(var(--p,0)*1.7rad)*5vmax) calc(cos(var(--p,0)*1.3rad)*4vmax)}
.r2{left:-6vmax;top:-8vmax;width:34vmax;height:34vmax;border:2px dashed rgba(11,138,78,.55);animation:spin 55s linear infinite reverse;--dc:var(--v);--d:16px;translate:calc(cos(var(--p,0)*1.1rad)*4vmax) calc(sin(var(--p,0)*1.9rad)*3vmax)}
.r2b{left:2vmax;top:0;width:18vmax;height:18vmax;border:6px double rgba(247,127,0,.4);animation:spin 38s linear infinite;--d:12px}
.r3{right:-17vmax;bottom:-19vmax;width:56vmax;height:56vmax;border:3px solid rgba(11,138,78,.55);background:radial-gradient(circle,rgba(11,138,78,.10),transparent 70%);animation:spin 95s linear infinite reverse;--dc:var(--v);translate:calc(cos(var(--p,0)*1.5rad)*5vmax) calc(sin(var(--p,0)*1.2rad)*4vmax)}
.r4{right:-6vmax;bottom:-9vmax;width:38vmax;height:38vmax;border:2px dashed rgba(247,127,0,.6);animation:spin 60s linear infinite;--d:18px;translate:calc(sin(var(--p,0)*1.4rad)*-4vmax) calc(cos(var(--p,0)*1.8rad)*3vmax)}
.r4b{right:3vmax;bottom:0;width:20vmax;height:20vmax;border:6px double rgba(11,138,78,.4);animation:spin 42s linear infinite reverse;--dc:var(--v);--d:12px}
.r5{right:7vw;top:20vh;width:15vmax;height:15vmax;border:2px solid rgba(247,127,0,.5);animation:spin 28s linear infinite;--d:12px;translate:calc(sin(var(--p,0)*2.1rad)*3vmax) calc(cos(var(--p,0)*1.6rad)*3vmax)}
.r6{left:5vw;bottom:24vh;width:11vmax;height:11vmax;border:2px dashed rgba(11,138,78,.55);animation:spin 24s linear infinite reverse;--dc:var(--v);--d:10px;translate:calc(cos(var(--p,0)*2.3rad)*3vmax) calc(sin(var(--p,0)*1.4rad)*3vmax)}
.bu{position:absolute;border-radius:50%;opacity:.5;animation:flot 9s ease-in-out infinite alternate}
.b1{left:12%;top:30%;width:26px;height:26px;background:var(--o)}.b2{left:30%;top:80%;width:18px;height:18px;background:var(--v);animation-delay:-2s}
.b3{left:72%;top:14%;width:20px;height:20px;background:var(--v);animation-delay:-4s}.b4{left:88%;top:62%;width:30px;height:30px;background:var(--o);animation-delay:-1s}
.b5{left:50%;top:90%;width:14px;height:14px;background:var(--r);animation-delay:-5s}.b6{left:60%;top:42%;width:12px;height:12px;background:var(--oc);animation-delay:-3s}
@keyframes flot{to{transform:translate(30px,-46px) scale(1.4)}}
.actif .entete:after{content:"";display:block;height:5px;width:0;border-radius:5px;background:linear-gradient(90deg,var(--o),var(--v));margin-top:12px;animation:tr2 1.2s 1.1s cubic-bezier(.2,.8,.2,1) forwards}
@keyframes tr2{to{width:min(260px,45%)}}
.actif .bloc{animation:bloc3 1.1s .9s cubic-bezier(.16,.95,.25,1) backwards}
@keyframes bloc3{from{opacity:0;transform:perspective(1200px) rotateX(14deg) translateY(60px) scale(.94)}to{opacity:1;transform:none}}
/* ===== Ouverture cinématique + transitions renforcées ===== */
.fond:before,.fond:after{content:"";position:absolute;border-radius:50%;width:60vmax;height:60vmax;will-change:transform}
.fond:before{left:-15vmax;top:-22vmax;background:radial-gradient(circle,rgba(247,127,0,.20),transparent 65%);animation:derive 16s ease-in-out infinite alternate}
.fond:after{right:-18vmax;bottom:-26vmax;background:radial-gradient(circle,rgba(11,138,78,.20),transparent 65%);animation:derive 20s ease-in-out infinite alternate-reverse}
@keyframes derive{to{transform:translate(16vmax,10vmax) scale(1.25)}}
#flash{position:fixed;inset:0;z-index:23;pointer-events:none;opacity:0;background:radial-gradient(circle at 50% 50%,#fff 0,rgba(255,205,130,.9) 28%,rgba(247,127,0,0) 62%)}
#flash.go{animation:fl 1.1s ease-out both}
@keyframes fl{0%{opacity:0;transform:scale(.3)}36%{opacity:1;transform:scale(1.1)}100%{opacity:0;transform:scale(1.9)}}
#ring{position:fixed;left:50%;top:50%;width:40px;height:40px;margin:-20px 0 0 -20px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 30px 6px var(--o),inset 0 0 30px 6px var(--o);opacity:0;z-index:23;pointer-events:none}
#ring.go{animation:rg 1.2s cubic-bezier(.15,.7,.3,1) both}
@keyframes rg{0%{opacity:1;transform:scale(.5)}100%{opacity:0;transform:scale(70)}}
#wipe.t6 b:nth-child(1){left:0;top:0;bottom:0;width:50.5%;background:linear-gradient(90deg,var(--vf),var(--v));animation:dl 1.3s cubic-bezier(.7,0,.3,1) both}
#wipe.t6 b:nth-child(2){right:0;top:0;bottom:0;width:50.5%;background:linear-gradient(270deg,var(--vf),var(--v));animation:dr 1.3s cubic-bezier(.7,0,.3,1) both}
#wipe.t6 b:nth-child(3){left:50%;top:50%;width:min(46vw,calc(var(--vh)*50));height:min(46vw,calc(var(--vh)*50));background:url(<?= BASE_URL ?>/assets/img/pres_logo.png?v=2) center/contain no-repeat;filter:drop-shadow(0 0 30px rgba(255,255,255,.7));animation:lg6 1.3s cubic-bezier(.2,1.2,.3,1) both}
#wipe.t6 b:nth-child(n+4){display:none}
@keyframes dl{0%{transform:translateX(-101%)}36%,60%{transform:none}100%{transform:translateX(-101%)}}
@keyframes dr{0%{transform:translateX(101%)}36%,60%{transform:none}100%{transform:translateX(101%)}}
@keyframes lg6{0%,22%{transform:translate(-50%,-50%) scale(0) rotate(-25deg);opacity:0}44%,60%{transform:translate(-50%,-50%) scale(1) rotate(0);opacity:1}82%,100%{transform:translate(-50%,-50%) scale(1.8);opacity:0}}
#wipe.t7 b:nth-child(1){inset:0;background:linear-gradient(135deg,var(--o),var(--r));animation:ic 1.25s cubic-bezier(.7,0,.3,1) both}
#wipe.t7 b:nth-child(2){inset:0;background:linear-gradient(135deg,var(--v),var(--vf));animation:ic 1.25s .14s cubic-bezier(.7,0,.3,1) both}
#wipe.t7 b:nth-child(n+3){display:none}
@keyframes ic{0%{clip-path:circle(0% at 0% 100%)}42%,58%{clip-path:circle(150% at 0% 100%);opacity:1}100%{clip-path:circle(150% at 0% 100%);opacity:0}}
.logowrap{position:relative;display:inline-block;isolation:isolate}
.rayons{position:absolute;left:50%;top:50%;width:min(100vw,calc(var(--vh)*105));aspect-ratio:1;margin:calc(min(100vw,calc(var(--vh)*105))/-2) 0 0 calc(min(100vw,calc(var(--vh)*105))/-2);border-radius:50%;opacity:0;z-index:-1;pointer-events:none;-webkit-mask-image:radial-gradient(circle,#000 0,transparent 62%);mask-image:radial-gradient(circle,#000 0,transparent 62%)}
.rayons:before{content:"";position:absolute;inset:0;border-radius:50%;background:repeating-conic-gradient(from 0deg,rgba(247,127,0,.34) 0 5deg,rgba(11,138,78,.16) 5deg 10deg,transparent 10deg 20deg);animation:spin 36s linear infinite}
.actif .rayons{animation:rin 1.8s .1s cubic-bezier(.2,.8,.2,1) both}
@keyframes rin{from{opacity:0;transform:scale(.1) rotate(-60deg)}to{opacity:1;transform:none}}
@keyframes spin{to{transform:rotate(360deg)}}
.onde{position:absolute;left:50%;top:50%;width:24px;height:24px;margin:-12px 0 0 -12px;border-radius:50%;border:3px solid var(--o);opacity:0;z-index:-1;pointer-events:none}
.actif .onde{animation:onde 3s var(--d,1s) ease-out infinite}
.onde:nth-of-type(2){border-color:var(--v)}
@keyframes onde{0%{transform:scale(1);opacity:.85}100%{transform:scale(26);opacity:0}}
.actif .ouv>.logowrap{animation:logoin 1.5s .45s cubic-bezier(.2,1.25,.3,1) both}
@keyframes logoin{from{opacity:0;transform:scale(.1) rotate(-30deg);filter:blur(14px)}55%{opacity:1;transform:scale(1.14) rotate(4deg);filter:none}to{opacity:1;transform:none;filter:none}}
.ouv .logo{filter:drop-shadow(0 10px 26px rgba(247,127,0,.45))}
.ouv h1 .w{perspective:700px}
.actif .ouv h1 .w i{animation:mot3 1.2s cubic-bezier(.16,.95,.25,1) both,coul 6s linear infinite;animation-delay:calc(1.3s + var(--d)*.09s),0s}
@keyframes mot3{from{transform:translateY(120%) rotateX(-85deg) scale(.6);filter:blur(10px)}to{transform:none;filter:none}}
.actif .ouv .trait{animation:tr 1.4s 2.8s ease forwards}
.invite{margin-top:clamp(14px,3vh,28px);font-weight:800;letter-spacing:.2em;text-transform:uppercase;font-size:clamp(10px,1.4vw,16px);color:var(--vf);opacity:0}
.actif .invite{animation:inv 1.2s 3.4s ease both,invp 2s 4.6s ease-in-out infinite}
@keyframes inv{from{opacity:0;transform:translateY(14px)}to{opacity:.85;transform:none}}
@keyframes invp{50%{opacity:.35;transform:translateX(8px)}}
/* ===== Budget : barres, montant, donut ===== */
.bars{display:grid;gap:clamp(5px,.9vh,10px);padding:clamp(10px,min(1.6vw,calc(var(--vh)*2.6)),24px)}
.br{display:grid;grid-template-columns:minmax(0,34%) minmax(0,1fr) auto;align-items:center;gap:clamp(8px,1.4vw,18px);font-size:clamp(12px,min(2vw,calc(var(--vh)*3.5)),34px);opacity:0;transform:translateX(-36px);transition:transform .25s}
.actif .br{animation:brin .8s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.5s + var(--k)*.12s)}
@keyframes brin{from{opacity:0;transform:translateX(-36px)}to{opacity:1;transform:none}}
.br .nm{font-weight:700;color:var(--vf);line-height:1.2}
.br .tr{height:clamp(10px,1.9vh,18px);border-radius:20px;background:#e9f3ed;overflow:hidden;position:relative}
.br .tr i{display:block;height:100%;width:0;border-radius:20px;background:linear-gradient(90deg,var(--v),var(--o));position:relative;overflow:hidden;transition:width 1.4s cubic-bezier(.2,.8,.2,1);transition-delay:calc(.7s + var(--k)*.12s)}
.actif .br .tr i{width:var(--w)}
.br .tr i:after{content:"";position:absolute;inset:0;background:linear-gradient(100deg,transparent 30%,rgba(255,255,255,.7) 50%,transparent 70%);transform:translateX(-120%)}
.actif .br .tr i:after{animation:lueur 3.2s calc(2.4s + var(--k)*.15s) ease-in-out infinite}
@keyframes lueur{0%{transform:translateX(-120%)}45%,100%{transform:translateX(120%)}}
.br.top .tr i{background:linear-gradient(90deg,var(--o),var(--r))}
.br .vl{font-weight:800;text-align:right;white-space:nowrap;color:var(--tx);min-width:9.5em}
.br .vl em{font-style:normal;font-weight:700;color:var(--o);margin-left:.6em;font-size:.85em}
.br:hover{transform:scale(1.015)}
.br:hover .tr i{filter:brightness(1.12) saturate(1.2)}
.br.tot{grid-template-columns:1fr auto;margin-top:clamp(4px,1vh,10px);padding:clamp(8px,1.4vh,14px) clamp(12px,2vw,24px);border-radius:16px;background:linear-gradient(100deg,var(--vf),var(--v) 45%,var(--o));color:#fff;box-shadow:0 12px 26px rgba(247,127,0,.35);font-size:clamp(15px,min(3vw,calc(var(--vh)*5.2)),52px)}
.br.tot .nm{color:#fff;letter-spacing:.06em;font-weight:900}
.br.tot .vl{color:#fff;font-weight:900;min-width:0}
.actif .br.tot{animation:brin .8s calc(.5s + 11*.12s) cubic-bezier(.16,.95,.25,1) both,puls 2.6s 3s ease-in-out infinite}
.grand{position:relative}
.grand:before{content:"";position:absolute;left:50%;top:52%;width:min(70vw,calc(var(--vh)*80));height:min(70vw,calc(var(--vh)*80));transform:translate(-50%,-50%);border-radius:50%;background:radial-gradient(circle,rgba(247,127,0,.22),rgba(11,138,78,.10) 45%,transparent 68%);z-index:-1;opacity:0}
.actif .grand:before{animation:halo 4s ease-in-out infinite;opacity:1}
@keyframes halo{0%,100%{transform:translate(-50%,-50%) scale(.85);opacity:.7}50%{transform:translate(-50%,-50%) scale(1.1);opacity:1}}
.montant{background:linear-gradient(90deg,var(--v),var(--o),var(--r),var(--o),var(--v));background-size:300% 100%;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;color:transparent;font-variant-numeric:tabular-nums;filter:drop-shadow(0 8px 18px rgba(247,127,0,.3))}
.actif .montant{animation:ap 1s .6s cubic-bezier(.16,.95,.25,1) both,coul 6s linear infinite}
.devise{font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--vf);font-size:clamp(11px,min(1.9vw,calc(var(--vh)*3.2)),22px);margin-top:14px}
.fin{display:flex;align-items:center;justify-content:center;gap:clamp(16px,4vw,56px);flex-wrap:wrap}
.dn{position:relative;width:min(38vw,calc(var(--vh)*46),420px);aspect-ratio:1;flex:0 0 auto}
.dn svg{width:100%;height:100%;transform:rotate(-90deg);overflow:visible;filter:drop-shadow(0 14px 22px rgba(11,138,78,.18))}
.dn circle{fill:none;stroke-width:26}
.dn .fond{stroke:#eaf3ee}
.dn .seg{stroke:var(--c);stroke-dasharray:0 600;stroke-dashoffset:var(--o);stroke-linecap:butt;transition:stroke-dasharray 1.5s cubic-bezier(.2,.8,.2,1),opacity .3s,stroke-width .3s;transition-delay:calc(.7s + var(--k)*.45s),0s,0s;cursor:pointer}
.actif .dn .seg{stroke-dasharray:var(--l) 600}
.dn[data-hl] .seg{opacity:.25}
.dn[data-hl="0"] .s0,.dn[data-hl="1"] .s1,.dn[data-hl="2"] .s2{opacity:1;stroke-width:32}
.dn .ct{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;line-height:1.1}
.dn .ct b{font-size:clamp(18px,min(3.4vw,calc(var(--vh)*6)),44px);font-weight:900;color:var(--vf);font-variant-numeric:tabular-nums}
.dn .ct small{font-weight:800;letter-spacing:.18em;color:var(--o);font-size:clamp(9px,1.2vw,15px)}
.lg{display:grid;gap:clamp(8px,1.6vh,16px);flex:1 1 320px;max-width:min(60vw,900px)}
.it{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:14px;background:#fff;border:1px solid #e3efe8;border-left:8px solid var(--c);border-radius:18px;padding:clamp(10px,1.8vh,18px) clamp(12px,1.6vw,22px);box-shadow:0 12px 26px rgba(11,138,78,.12);opacity:0;cursor:pointer;transition:transform .25s,box-shadow .25s}
.actif .it{animation:brin .9s cubic-bezier(.16,.95,.25,1) both;animation-delay:calc(.9s + var(--k)*.35s)}
.it:hover,.it.on{transform:translateX(8px) scale(1.02);box-shadow:0 18px 34px rgba(247,127,0,.25)}
.it .pt{width:16px;height:16px;border-radius:50%;background:var(--c);box-shadow:0 0 0 5px color-mix(in srgb,var(--c) 22%,transparent)}
.it h4{font-size:clamp(15px,min(2.2vw,calc(var(--vh)*3.9)),38px);font-weight:800;color:var(--vf);line-height:1.2}
.it p{font-size:clamp(12px,min(1.6vw,calc(var(--vh)*2.8)),26px);color:#5a7364;margin-top:3px}
.it .mt{text-align:right;font-weight:900;font-size:clamp(16px,min(2.5vw,calc(var(--vh)*4.4)),44px);color:var(--tx);white-space:nowrap;font-variant-numeric:tabular-nums}
.it .mt em{display:block;font-style:normal;color:var(--c);font-size:.78em;font-weight:800}
.slogan{background-size:300% 100%}
@media(orientation:portrait){.br{grid-template-columns:1fr auto;grid-template-areas:"nm vl" "tr tr"}.br .nm{grid-area:nm}.br .vl{grid-area:vl;min-width:0}.br .tr{grid-area:tr}.br.tot{grid-template-columns:1fr auto;grid-template-areas:none;gap:8px}.br.tot .nm,.br.tot .vl{grid-area:auto;font-size:.8em}.br.tot .nm{letter-spacing:.02em}.dn{width:min(70vw,calc(var(--vh)*34))}.lg{flex-basis:100%}}
.titrebud{font-size:clamp(22px,min(5vw,calc(var(--vh)*8)),62px)!important;margin-top:12px}
.montant{font-size:clamp(46px,min(14vw,calc(var(--vh)*22)),180px);font-weight:900;line-height:1.05;margin-top:18px;letter-spacing:.01em}
.montant{background:linear-gradient(90deg,var(--v),var(--o),var(--r));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.montant small{font-size:.45em;font-weight:800}
.slogan{font-size:clamp(28px,min(7.5vw,calc(var(--vh)*13)),96px)!important;margin-top:18px;line-height:1.15}
.res.deux li:last-child{font-size:1.35em;font-weight:900;color:var(--vf);list-style:none}
.res.deux li:last-child strong{color:var(--o)}
.titreintro{font-size:clamp(20px,min(4.2vw,calc(var(--vh)*7.5)),54px)!important;margin-top:14px}
.bloc li strong{color:var(--vf)}
@media(min-width:700px){.res.deux ul{grid-auto-flow:column;grid-template-rows:repeat(6,auto);column-gap:clamp(16px,3vw,40px)}}
@media(orientation:portrait){.titreintro{font-size:clamp(20px,5.6vw,46px)!important}}
/* merci */
.merci{text-align:center}
.merci .logo{width:clamp(90px,calc(var(--vh)*24),260px);animation:flo 5s ease-in-out infinite}
.merci h1{font-size:clamp(48px,min(13vw,calc(var(--vh)*24)),170px);font-weight:900;line-height:1;letter-spacing:.04em}
.merci p{font-size:clamp(16px,min(3vw,calc(var(--vh)*5)),38px);font-weight:700;color:var(--vf);margin-top:14px;line-height:1.3}
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
.etincelle{position:fixed;width:12px;height:12px;border-radius:50%;pointer-events:none;z-index:40;animation:etc .8s ease-out forwards}
@keyframes etc{to{transform:translate(var(--dx),var(--dy)) scale(0);opacity:0}}
#confetti{position:fixed;inset:0;pointer-events:none;z-index:35}
/* mesure : état final (tout affiché) pour ajuster la taille à l'écran */
.fit .cols{transition:none!important;row-gap:14px}
.fit .res{display:block!important;height:auto!important;opacity:1!important;overflow:visible!important;padding:clamp(12px,min(2.2vw,3.5vh),30px)!important;border-width:1px!important;transition:none!important}
.fit .el{transform:none!important;filter:none!important;animation:none!important}
.fit .w i{transform:none!important;animation:none!important}
.fit .contenu{animation:none!important}
.fit .objb,.fit .bloc li{opacity:1!important;transition:none!important}
.fit .cols:not(.solo) .objb{display:none!important}
.fit .cols{grid-template-columns:1fr!important;column-gap:0!important}
.cols.s2:not(.solo){grid-template-columns:0fr 1fr;column-gap:0}
.cols.s2:not(.solo) .objb{max-height:0;align-self:start;opacity:0;transform:translateX(-70px);padding:0;border-width:0;box-shadow:none;pointer-events:none;transition:opacity .6s,transform .9s cubic-bezier(.16,.95,.25,1),padding .9s,border-width .9s}
@media(max-width:820px){.cols.s2:not(.solo){grid-template-columns:1fr;row-gap:0}.cols.s2:not(.solo) .objb{display:none}}
@media(max-width:820px){.cols{grid-template-columns:1fr;row-gap:0}.cols.s2{grid-template-columns:1fr;row-gap:14px}.res{display:none}.s2 .res{display:block}.slide{align-items:flex-start}.points{display:none}}
@media(max-height:520px) and (orientation:landscape){.slide{bottom:66px}.nav button,.nav a{width:42px;height:42px}.partie .logo,.merci .logo{width:90px}}
@media(orientation:portrait){.bloc .obj{font-size:clamp(19px,5vw,40px)}.bloc li{font-size:clamp(18px,4.6vw,36px)}.entete h2{font-size:clamp(24px,7vw,60px)}.entete p{font-size:clamp(16px,4vw,30px)}.bloc h3{font-size:clamp(13px,3.2vw,22px)}.partie h1{font-size:clamp(26px,8vw,68px)}}
@media(prefers-reduced-motion:reduce){*{animation-duration:.01s!important;animation-delay:0s!important;transition-duration:.01s!important}}

/* ===== Fiches responsables ===== */
.fiche{display:flex;align-items:center;justify-content:center;gap:clamp(18px,4vw,64px);flex-wrap:wrap}
.ph{position:relative;width:min(34vw,calc(var(--vh)*46),460px);aspect-ratio:1;flex:0 0 auto;padding:10px;border-radius:34px;background:linear-gradient(135deg,var(--o),var(--v),var(--r),var(--o),var(--v));background-size:300% 300%;box-shadow:0 22px 50px rgba(247,127,0,.35)}
.actif .ph{animation:phin 1.3s .5s cubic-bezier(.2,1.2,.3,1) both,gp 5s linear infinite}
@keyframes phin{from{opacity:0;transform:scale(.3) rotate(-14deg)}to{opacity:1;transform:none}}
@keyframes gp{to{background-position:300% 300%}}
.ph img,.ph .ini{width:100%;height:100%;object-fit:cover;border-radius:26px;display:block;background:#fff}
.ph .ini{display:flex;align-items:center;justify-content:center;font-size:clamp(50px,12vw,160px);font-weight:900;color:var(--o)}
.info{flex:1 1 380px;min-width:0;max-width:980px}
.com{display:inline-block;background:linear-gradient(135deg,var(--o),var(--r));color:#fff;font-weight:900;letter-spacing:.08em;text-transform:uppercase;border-radius:14px;padding:.3em .9em;font-size:clamp(14px,min(2.4vw,calc(var(--vh)*4.2)),40px);box-shadow:0 8px 18px rgba(247,127,0,.35)}
.actif .com{animation:ap 1s .7s cubic-bezier(.16,.95,.25,1) both,puls 2.6s 2.4s ease-in-out infinite}
.nomf{font-size:clamp(26px,min(5vw,calc(var(--vh)*9)),96px);font-weight:900;text-transform:uppercase;line-height:1.05;margin:.25em 0 .1em}
.rowf{background:#fff;border:1px solid #e3efe8;border-left:8px solid var(--o);border-radius:16px;padding:.45em 1em;margin-top:clamp(8px,1.6vh,18px);box-shadow:0 12px 26px rgba(11,138,78,.12);opacity:0;transition:transform .25s}
.actif .rowf{animation:brin .9s calc(1.2s + var(--k)*.4s) cubic-bezier(.16,.95,.25,1) both}
.rowf:hover{transform:translateX(8px) scale(1.01)}
.rowf small{display:block;font-size:clamp(11px,min(1.6vw,calc(var(--vh)*2.8)),26px);letter-spacing:.14em;color:var(--o);font-weight:900;text-transform:uppercase}
.rowf b{display:block;font-size:clamp(18px,min(3.2vw,calc(var(--vh)*5.6)),60px);font-weight:700;color:var(--tx);line-height:1.2}
.rowf b em{font-style:normal;color:var(--v);font-size:.7em;font-weight:800;margin-left:.5em}
.vide{text-align:center}
.vide h2{font-size:clamp(24px,5vw,60px);font-weight:900;color:var(--vf)}
</style>
</head>
<body>
<div class="fond" id="fond">
  <div class="par p1"><div class="cer r1"></div><div class="cer r2"></div><div class="cer r2b"></div></div>
  <div class="par p2"><div class="cer r3"></div><div class="cer r4"></div><div class="cer r4b"></div></div>
  <div class="par p3"><div class="cer r5"></div><div class="cer r6"></div></div>
  <div class="par p4"><i class="bu b1"></i><i class="bu b2"></i><i class="bu b3"></i><i class="bu b4"></i><i class="bu b5"></i><i class="bu b6"></i></div>
</div>
<div class="brille"></div>
<div class="barre" id="barre"></div>
<div id="flash"></div><div id="ring"></div>
<div id="wipe"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b></div>
<div class="haut"><img src="<?= BASE_URL ?>/assets/img/pres_bandeau.png" alt="AEEMCI — Secrétariat Régional Abidjan Nord — Sous-comités Anyama 1 &amp; 2 — JOSPIA"></div>
<div id="scene">
  <section class="slide" data-steps="1"><div class="contenu partie ouv">
    <div class="el logowrap" style="--i:0"><span class="rayons"></span><i class="onde"></i><i class="onde" style="--d:1.5s"></i><img class="logo" src="<?= BASE_URL ?>/assets/img/pres_logo.png?v=2" alt="JOSPIA 2026"></div>
    <h1 class="degrade titreintro"><?= mots('PRÉSENTATION DES RESPONSABLES DE COMMISSION', 1) ?></h1>
    <div class="trait"></div>
    <p class="invite">Appuyez sur &#8594; pour commencer</p>
  </div></section>
<?php if (!$responsables): ?>
  <section class="slide" data-steps="1"><div class="contenu vide">
    <h2 class="degrade"><?= mots('AUCUNE FICHE REÇUE POUR LE MOMENT') ?></h2>
    <p class="el" style="--i:4">Les responsables de commission remplissent leur fiche sur <strong>/inscription_responsable</strong></p>
  </div></section>
<?php endif; ?>
<?php foreach ($responsables as $r):
  $ini = mb_strtoupper(mb_substr(trim($r['nom_prenoms']), 0, 1));
  $ans = $r['annee_debut'] ? max(0, (int)date('Y') - (int)$r['annee_debut']) : null; ?>
  <section class="slide" data-steps="1"><div class="contenu fiche">
    <div class="ph"><?php if ($r['photo'] !== ''): ?><img src="<?= BASE_URL ?>/uploads/photos/<?= e(rawurlencode($r['photo'])) ?>" alt="<?= e($r['nom_prenoms']) ?>"><?php else: ?><div class="ini"><?= e($ini) ?></div><?php endif; ?></div>
    <div class="info">
      <span class="com">Commission <?= e($r['commission']) ?></span>
      <h2 class="nomf degrade"><?= mots($r['nom_prenoms'], 2) ?></h2>
      <div class="rowf" style="--k:0"><small>Profil au sein de la commission</small><b><?= e($r['profil']) ?></b></div>
      <div class="rowf" style="--k:1"><small>Militant(e) depuis</small><b><?= e($r['annee_debut']) ?><?php if ($ans): ?><em>(<?= $ans ?> an<?= $ans > 1 ? 's' : '' ?>)</em><?php endif; ?></b></div>
      <div class="rowf" style="--k:2"><small>Niveau d'études / fonction</small><b><?= e($r['niveau_fonction']) ?></b></div>
    </div>
  </div></section>
<?php endforeach; ?>
  <section class="slide final" data-steps="1"><div class="contenu merci">
    <div class="el" style="--i:0"><img class="logo" src="<?= BASE_URL ?>/assets/img/pres_logo.png?v=2" alt="JOSPIA 2026"></div>
    <h1 class="degrade"><?= mots('MERCI') ?></h1>
    <p class="el" style="--i:6">Merci à tous les responsables pour leur engagement.</p>
    <div class="trait"></div>
  </div></section>
</div>
<canvas id="confetti"></canvas>
<div class="bas">
  <div class="nav"><button id="prec" type="button" aria-label="Précédent">&#8592;</button><button id="suiv" type="button" aria-label="Suivant">&#8594;</button></div>
  <div class="points" id="points"></div>
  <div class="nav"><?php if (!defined('PRESENTATION_PUBLIQUE')): ?><a class="sec" href="<?= BASE_URL ?>/admin/comite_manageriale" title="Quitter" aria-label="Quitter">&#10005;</a><?php endif; ?><button class="sec" id="plein" type="button" title="Plein écran" aria-label="Plein écran">&#x26F6;</button><div class="cpt" id="cpt"></div></div>
</div>
<script>
(function(){
  function vh(){document.documentElement.style.setProperty('--vh',(window.innerHeight/100)+'px')}
  vh();addEventListener('resize',vh);addEventListener('orientationchange',vh);
  var sl=[].slice.call(document.querySelectorAll('.slide')),n=sl.length,c=-1,st=0;
  var pts=document.getElementById('points'),cpt=document.getElementById('cpt'),bar=document.getElementById('barre'),bal=document.getElementById('wipe'),busy=false;
  var NT=8,fl=document.getElementById('flash'),rg=document.getElementById('ring');
  function ajuster(){
    sl.forEach(function(s){
      var c=s.querySelector('.contenu');if(!c)return;
      s.classList.add('fit');c.style.zoom=1;
      function ok(){return c.getBoundingClientRect().height<=s.clientHeight-4}
      if(!ok()){var lo=.25,hi=1;for(var t=0;t<12;t++){var m=(lo+hi)/2;c.style.zoom=m;if(ok())lo=m;else hi=m}c.style.zoom=lo}
      s.classList.remove('fit');
      if(window.CSS&&CSS.supports&&CSS.supports('zoom','1'))s.style.overflowY='hidden';
    });
  }
  var rt;function replanifier(){clearTimeout(rt);rt=setTimeout(ajuster,120)}
  addEventListener('resize',replanifier);addEventListener('orientationchange',replanifier);
  if(document.fonts&&document.fonts.ready)document.fonts.ready.then(ajuster);
  sl.forEach(function(s,i){var d=document.createElement('span');d.onclick=function(){if(!busy)go(i,0)};pts.appendChild(d)});
  function fm(n){return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g,'.')}
  function cu(el){var v=+el.getAttribute('data-cu'),d=+el.getAttribute('data-d')||0,t0=null;el.textContent='0';
    if(el._r)cancelAnimationFrame(el._r);
    function f(t){if(t0===null)t0=t;var p=(t-t0-d)/1700;if(p<0){el._r=requestAnimationFrame(f);return}p=Math.min(1,p);el.textContent=fm(v*(1-Math.pow(1-p,3)));if(p<1)el._r=requestAnimationFrame(f)}
    el._r=requestAnimationFrame(f)}
  function maj(){
    var s=sl[c],cols=s.querySelector('.cols');
    if(cols){var solo=cols.classList.contains('solo'),off=solo?1:2;
      cols.classList.toggle('s1',st>=1);cols.classList.toggle('s2',st>=off);
      [].forEach.call(cols.querySelectorAll('li'),function(li,k){li.classList.toggle('on',st>=k+off)});
      var cible=st>=off?cols.querySelectorAll('li')[st-off]:(st===1?cols.querySelector('.objb'):null);
      if(cible)setTimeout(function(){try{cible.scrollIntoView({behavior:'smooth',block:'nearest'})}catch(e){}},700)}
    document.getElementById('prec').disabled=(c===0&&st===0);
    document.getElementById('suiv').disabled=(c===n-1);
  }
  function go(i,step,direct){
    if(i<0||i>=n)return;
    if(i===c){st=step;maj();return}
    function bascule(){
      sl.forEach(function(s,k){s.classList.toggle('actif',k===i);s.classList.remove('e1','e2','e3');if(k===i&&i%4)s.classList.add('e'+(i%4));if(k===i)s.scrollTop=0;if(k!==i){var cc=s.querySelector('.cols');if(cc){cc.classList.remove('s1','s2');[].forEach.call(cc.querySelectorAll('li'),function(li){li.classList.remove('on')})}}});
      [].forEach.call(pts.children,function(p,k){p.classList.toggle('on',k===i)});
      cpt.textContent=(i+1)+' / '+n;
      document.getElementById('fond').style.setProperty('--p',i);
      bar.style.width=((i+1)/n*100)+'%';
      try{history.replaceState(null,'','#'+(i+1))}catch(e){}
      [].forEach.call(sl[i].querySelectorAll('[data-cu]'),cu);
      if(sl[i].querySelector('.ouv'))setTimeout(function(){if(c!==i)return;var r=sl[i].querySelector('.logo').getBoundingClientRect();burst(r.left+r.width/2,r.top+r.height/2,90)},direct?700:1500);
      else if(sl[i].querySelector('.partie'))setTimeout(function(){if(c!==i)return;var r=sl[i].querySelector('.logo').getBoundingClientRect();burst(r.left+r.width/2,r.top+r.height/2,45)},1500);
      if(sl[i].classList.contains('final'))setTimeout(fete,1100);
      c=i;st=step;maj();
    }
    if(direct||c<0){bascule();return}
    var T=i%NT;busy=true;bal.className='';fl.className='';rg.className='';void bal.offsetWidth;bal.className='go t'+T;fl.className='go';rg.className='go';
    setTimeout(bascule,T===4?420:560);setTimeout(function(){bal.className='';busy=false},1500);
  }
  function suivant(){
    if(busy)return;
    var max=parseInt(sl[c].getAttribute('data-steps'),10)-1;
    if(st<max){st++;maj()}else go(c+1,0);
  }
  function precedent(){
    if(busy)return;
    if(st>0){st--;maj()}
    else if(c>0){var p=sl[c-1],m=parseInt(p.getAttribute('data-steps'),10)-1;go(c-1,m)}
  }
  document.getElementById('prec').onclick=precedent;
  document.getElementById('suiv').onclick=suivant;
  document.addEventListener('keydown',function(e){
    if(['ArrowRight','PageDown',' ','Enter'].indexOf(e.key)>=0){e.preventDefault();suivant()}
    else if(['ArrowLeft','PageUp','Backspace'].indexOf(e.key)>=0){e.preventDefault();precedent()}
    else if(e.key==='Home')go(0,0);else if(e.key==='End')go(n-1,0);
    else if(e.key==='f'||e.key==='F')pl();
  });
  var x0=null,y0=null;
  document.addEventListener('touchstart',function(e){x0=e.touches[0].clientX;y0=e.touches[0].clientY},{passive:true});
  document.addEventListener('touchend',function(e){
    if(x0===null)return;var dx=e.changedTouches[0].clientX-x0,dy=e.changedTouches[0].clientY-y0;x0=null;
    if(Math.abs(dx)>60&&Math.abs(dx)>Math.abs(dy)*1.5){dx<0?suivant():precedent()}
  },{passive:true});
  function pl(){var d=document.documentElement;
    if(!document.fullscreenElement){(d.requestFullscreen||d.webkitRequestFullscreen||function(){}).call(d)}
    else{(document.exitFullscreen||document.webkitExitFullscreen).call(document)}}
  document.getElementById('plein').onclick=pl;
  document.addEventListener('mousemove',function(e){var f=document.getElementById('fond');f.style.setProperty('--mx',(e.clientX/innerWidth-.5).toFixed(3));f.style.setProperty('--my',(e.clientY/innerHeight-.5).toFixed(3))});
  // interactivité : bulles qui suivent la souris, étincelles au clic, cartes inclinées
  document.addEventListener('mousemove',function(e){
    var x=e.clientX/innerWidth-.5,y=e.clientY/innerHeight-.5;
    var bl=e.target.closest&&e.target.closest('.bloc');
    if(bl){var r=bl.getBoundingClientRect();var px=(e.clientX-r.left)/r.width-.5,py=(e.clientY-r.top)/r.height-.5;bl.style.transform='perspective(900px) rotateY('+(px*8)+'deg) rotateX('+(-py*8)+'deg) translateY(-4px)'}
  });
  [].forEach.call(document.querySelectorAll('.bloc'),function(b){b.addEventListener('mouseleave',function(){b.style.transform=''})});
  var coul=['#F77F00','#0B8A4E','#E8431E','#FFA43A','#12A862'];
  document.addEventListener('click',function(e){
    if(e.target.closest('.bas'))return;
    for(var k=0;k<12;k++){var s=document.createElement('div');s.className='etincelle';var a=Math.random()*6.28,d=40+Math.random()*70;
      s.style.cssText='left:'+e.clientX+'px;top:'+e.clientY+'px;background:'+coul[k%5]+';--dx:'+Math.cos(a)*d+'px;--dy:'+Math.sin(a)*d+'px';
      document.body.appendChild(s);setTimeout(function(x){x.remove()},900,s)}
  });
  var dn=document.getElementById('dn');
  if(dn){[].forEach.call(document.querySelectorAll('.it,.dn .seg'),function(x){
    x.addEventListener('mouseenter',function(){var k=x.getAttribute('data-k');dn.setAttribute('data-hl',k);[].forEach.call(document.querySelectorAll('.it'),function(i){i.classList.toggle('on',i.getAttribute('data-k')===k)})});
    x.addEventListener('mouseleave',function(){dn.removeAttribute('data-hl');[].forEach.call(document.querySelectorAll('.it'),function(i){i.classList.remove('on')})})})}
  // confettis de la fin
  var cv=document.getElementById('confetti'),cx=cv.getContext('2d'),P=[],run=false;
  function fete(){
    cv.width=innerWidth;cv.height=innerHeight;
    for(var k=0;k<170;k++)P.push({x:Math.random()*cv.width,y:-20-Math.random()*cv.height*.6,w:6+Math.random()*8,h:10+Math.random()*10,vx:-1.5+Math.random()*3,vy:2+Math.random()*4,r:Math.random()*6,vr:-.2+Math.random()*.4,c:coul[k%5]});
    if(!run){run=true;boucle()}
  }
  function burst(x,y,n){
    if(cv.width!==innerWidth){cv.width=innerWidth;cv.height=innerHeight}
    for(var k=0;k<n;k++){var a=Math.random()*6.283,v=3+Math.random()*9;
      P.push({x:x,y:y,w:4+Math.random()*5,h:4+Math.random()*5,vx:Math.cos(a)*v,vy:Math.sin(a)*v-2,r:Math.random()*6,vr:-.3+Math.random()*.6,c:coul[k%5],g:.16,l:1,dec:.014+Math.random()*.012})}
    if(!run){run=true;boucle()}
  }
  function boucle(){
    cx.clearRect(0,0,cv.width,cv.height);
    P=P.filter(function(p){return p.y<cv.height+30&&(p.l===undefined||p.l>0)});
    P.forEach(function(p){if(p.g){p.vy+=p.g;p.vx*=.985;p.l-=p.dec}p.x+=p.vx;p.y+=p.vy;p.r+=p.vr;cx.save();cx.globalAlpha=p.g?Math.max(0,p.l):1;cx.translate(p.x,p.y);cx.rotate(p.r);cx.fillStyle=p.c;cx.fillRect(-p.w/2,-p.h/2,p.w,p.h);cx.restore()});
    if(P.length)requestAnimationFrame(boucle);else run=false;
  }
  ajuster();
  var h=parseInt((location.hash||'').slice(1),10);
  go(h>=1&&h<=n?h-1:0,0,true);
})();
</script>
</body>
</html>
