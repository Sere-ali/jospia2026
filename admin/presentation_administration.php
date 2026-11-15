<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin']);
require_once __DIR__ . '/../includes/presentation_admin.php';

$contenuPres = presAdminLire();
$stats = presAdminChiffres();
$slidesAdmin = presAdminDiapositives($contenuPres, $stats);
$membresAdmin = presAdminMembres();

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


/* ---------- Le site JOSPIA : explication page par page ---------- */
$SITE = [
 ['FRONT-END', "L'ACCUEIL", '/', [
   ["Présentation", "L'événement, les dates et le lieu"],
   ["3 étapes", "S'inscrire, payer, passer le test"],
   ["Accès direct", "Formulaires séminariste, commission, visiteur"],
   ["Mobile", "Adapté au téléphone et installable"]]],
 ['FRONT-END', "L'INSCRIPTION DES SÉMINARISTES", '/inscription_seminariste', [
   ["Formulaire", "Identité, niveau, sous-comité, section"],
   ["Photo", "Téléversée en ligne avec le dossier"],
   ["Matricule", "JOS-001, JOS-002… attribué automatiquement"],
   ["Dortoir", "Hommes, Femmes ou Pépinière, affecté automatiquement"]]],
 ['FRONT-END', "L'INSCRIPTION DES COMMISSIONS", '/inscription_commission', [
   ["Commission", "Choix parmi les 13 commissions"],
   ["Identité", "Nom, contact et photo"],
   ["Badge", "Généré automatiquement"],
   ["Compte", "Identifiants créés automatiquement"]]],
 ['FRONT-END', "LE PAIEMENT ET SON SUIVI", '/paiement  ·  /statut', [
   ["Paiement", "Déclaré en ligne, réglé en espèces"],
   ["Reçu", "Avec QR code unique"],
   ["Suivi", "Statut visible à tout moment"],
   ["Validation", "Confirmée par la commission Finance"]]],
 ['FRONT-END', "LE TEST D'ENTRÉE", '/quiz', [
   ["20 questions", "Coran, Fiqh, Aqida, culture, AEEMCI"],
   ["Minuteur", "20 minutes, correction automatique"],
   ["Note sur 20", "Niveau attribué : Primaire à Leader"],
   ["Correction", "Détaillée, question par question"],
   ["Diplôme", "Généré automatiquement"]]],
 ['FRONT-END', "LES VISITEURS", '/visiteur', [
   ["Arrivée", "Enregistrement du visiteur"],
   ["Sortie", "Heure de départ enregistrée"],
   ["Suivi", "Consultable par la commission Sécurité"]]],
 ['FRONT-END', "L'ESPACE PERSONNEL", '/espace', [
   ["Fiche", "D'inscription imprimable"],
   ["Badge", "Aperçu et téléchargement"],
   ["Bulletin", "Notes et niveau obtenus"],
   ["Services", "Critiques, rapports, sortie du camp"]]],
 ['FRONT-END', "LA CONNEXION ET LE COMPTE", '/login  ·  /compte', [
   ["Identifiants", "Générés à l'inscription"],
   ["Rôles", "Séminariste, membre, admin, finance, sécurité…"],
   ["Sécurité", "Mots de passe chiffrés"],
   ["Compte", "Changement du mot de passe"]]],
 ['BACK-END', "LE TABLEAU DE BORD", '/admin/dashboard', [
   ["En direct", "Inscrits, affectations, tests complétés"],
   ["Finances", "Encaissements et paiements en attente"],
   ["Répartitions", "Par commission, sous-comité et section"],
   ["Niveaux", "Résultats du test d'entrée"]]],
 ['BACK-END', "L'ADMINISTRATION", '/admin/administration', [
   ["Inscrits", "Consulter, modifier, supprimer"],
   ["Dortoirs", "Listes imprimables par dortoir"],
   ["Documents", "Badges et diplômes en lot"],
   ["Comptes", "Comptes admin et paramètres du site"]]],
 ['BACK-END', "LA COMMISSION SCIENTIFIQUE", '/admin/commission_scientifique', [
   ["Notes", "Saisie par matière et par séminariste"],
   ["Bulletins", "Calcul et impression automatiques"],
   ["Test d'entrée", "Questions, matières, seuils de niveau"],
   ["Programme", "Programme journalier et archives"]]],
 ['BACK-END', "LA COMMISSION FINANCE", '/finance', [
   ["Paiements", "Validation des paiements en attente"],
   ["Scanner", "Vérification des reçus par QR code"],
   ["Tickets", "Génération et impression des tickets"]]],
 ['BACK-END', "LA COMMISSION SÉCURITÉ", '/securite', [
   ["Visiteurs", "Contrôle des arrivées et des sorties"],
   ["Sorties du camp", "Autorisation et suivi des demandes"],
   ["Retours", "Historique des retours"]]],
 ['BACK-END', "LE COMITÉ MANAGÉRIAL", '/admin/comite_manageriale', [
   ["Rapports", "Rapports des commissions"],
   ["Sorties", "Autorisations de sortie du camp"],
   ["Présentations", "Lancement et bilans projetés"]]],
 ['TECHNIQUE', "LES COULISSES DU SITE", 'jospia26.site', [
   ["Technologies", "PHP 8.2 et base de données MariaDB"],
   ["Hébergement", "Docker sur Render, en ligne 24h/24"],
   ["Déploiement", "Automatique à chaque mise à jour"],
   ["Protection", "Accès par rôle et journal d'activité"],
   ["Documents", "PDF, QR codes et badges générés par le site"]]],
];


function afficherEquipe($membres) {
    $pages = array_chunk($membres, 8); $tot = count($membres);
    foreach ($pages as $pi => $pg): $n = count($pg); ?>
  <section class="slide site equipe" data-steps="<?= $n + 1 ?>"><div class="cadre">
    <header class="tete"><div class="kz a" style="--d:0"><?= $tot ?> MEMBRES</div></header>
    <h2 class="sh"><?= mots($pi === 0 ? "L'ÉQUIPE DE LA COMMISSION ADMINISTRATION" : "L'ÉQUIPE : SUITE", 1) ?></h2>
    <div class="eq">
      <?php foreach ($pg as $k => $m): $ini = ''; foreach (preg_split('/\s+/', trim($m['nom'])) as $w) { if ($w !== '' && mb_strlen($ini) < 2) $ini .= mb_strtoupper(mb_substr($w, 0, 1)); } ?>
      <div class="mb<?= $m['resp'] ? ' rs' : '' ?>">
        <div class="ph"><?php if ($m['src'] !== ''): ?><img src="<?= e($m['src']) ?>" alt=""><?php else: ?><span><?= e($ini) ?></span><?php endif; ?></div>
        <h4><?= e($m['nom']) ?></h4>
        <small><?= $m['resp'] ? 'RESPONSABLE' : 'MEMBRE' ?></small>
      </div>
      <?php endforeach; ?>
    </div>
  </div></section>
<?php endforeach; }

function afficherAnnonceMembres($membres) {
    $f = array_values(array_filter($membres, function ($m) { return !empty($m['fiche']); })); $n = count($f);
    if (!$n) return; ?>
  <section class="slide ann"><div class="cadre">
    <div class="kz a" style="--d:0">NOTRE ÉQUIPE</div>
    <div class="bign a" style="--d:2"><b data-n="<?= $n ?>">0</b></div>
    <h2 class="annt"><?= mots($n > 1 ? "MEMBRES DANS LA COMMISSION ADMINISTRATION" : "MEMBRE DANS LA COMMISSION ADMINISTRATION", 4) ?></h2>
    <p class="anns a" style="--d:9">Une équipe constituée et engagée au service de la JOSPIA</p>
    <div class="avs">
      <?php foreach ($f as $k => $m): ?><div class="av" style="--k:<?= $k ?>"><img src="<?= e($m['src']) ?>" alt=""></div><?php endforeach; ?>
    </div>
  </div></section>
<?php }

function afficherProfils($membres) {
    foreach ($membres as $m) { if (empty($m['fiche'])) continue;
        $an = (int)($m['annee'] ?? 0); $dur = $an ? max(1, (int)date('Y') - $an) : 0;
        $lignes = [];
        if (!empty($m['metier'])) $lignes[] = ['PROFESSION', $m['metier']];
        elseif (!empty($m['etudes'])) $lignes[] = ['PARCOURS', $m['etudes']];
        elseif (!empty($m['classe'])) $lignes[] = ['CLASSE', 'Élève en classe de ' . $m['classe']];
        if (!empty($m['profil'])) $lignes[] = ['PROFIL DANS LA COMMISSION', $m['profil']];
        if ($an) $lignes[] = ['DÉBUT DU MILITANTISME', $an . '  ·  ' . $dur . ' an' . ($dur > 1 ? 's' : '') . ' d\'engagement'];
        ?>
  <section class="slide site prof<?= mb_strlen($m['nom']) > 18 ? ' lg' : '' ?>" data-steps="<?= count($lignes) + 1 ?>"><div class="cadre">
    <header class="tete"><div class="kz a" style="--d:0"><?= !empty($m['resp']) ? 'RESPONSABLE DE LA COMMISSION' : 'MEMBRE DE LA COMMISSION' ?></div></header>
    <div class="pfo a" style="--d:2"><i class="r1"></i><i class="r2"></i><div class="pfi"><img src="<?= e($m['src']) ?>" alt=""></div></div>
    <h2 class="pfn"><?= mots(mb_strtoupper($m['nom']), 2) ?></h2>
    <ul class="prs">
      <?php foreach ($lignes as $l): ?><li class="pr"><small><?= e($l[0]) ?></small><strong><?= e($l[1]) ?></strong></li><?php endforeach; ?>
    </ul>
  </div></section>
<?php } }

function afficherSiteArchi() { ?>
  <section class="slide site arch" data-steps="5"><div class="cadre">
    <header class="tete"><div class="kz a" style="--d:0">LE SITE JOSPIA</div></header>
    <h2 class="sh"><?= mots("RÉALISATIONS : LE SITE JOSPIA", 1) ?></h2>
    <div class="ar">
      <?php foreach ([
        ['FRONT-END', 'Ce que voit l\'utilisateur', ['Pages et formulaires', 'Design adapté mobile', 'Espace personnel'], 'f'],
        ['BACK-END', 'Le cerveau du site', ['Règles et calculs', 'Rôles et sécurité', 'PDF, badges, QR codes'], 'b'],
        ['DONNÉES', 'La base de données du site', ['Inscrits et paiements', 'Notes et bulletins', 'Journal d\'activité'], 't'],
      ] as $i => [$t, $d, $l, $c]): ?>
      <div class="ac <?= $c ?>"><b><?= $i + 1 ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p><ul><?php foreach ($l as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
      <?php if ($i < 2): ?><div class="fl"><u></u><u></u><u></u></div><?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="bandeau-s">Du clic de l'utilisateur jusqu'à l'enregistrement des données</div>
  </div></section>
<?php }

function afficherSite($zone, $titre, $url, $pts) {
    $n = count($pts); $cl = $zone === 'FRONT-END' ? 'f' : ($zone === 'BACK-END' ? 'b' : 't');
    ?>
  <section class="slide site pg <?= $cl ?> n<?= $n ?>" data-steps="<?= $n + 1 ?>"><div class="cadre">
    <header class="tete"><div class="kz a" style="--d:0"><?= e($zone) ?></div></header>
    <h2 class="sh"><?= mots($titre, 1) ?></h2>
    <div class="mock a" style="--d:3">
      <div class="bar"><i></i><i></i><i></i><div class="url"><span style="--len:<?= mb_strlen($url) ?>"><?= e($url) ?></span></div></div>
      <div class="corps">
        <div class="nav"><u></u><u class="ac"></u><u></u><u></u><s></s></div>
        <?php foreach ($pts as $k => $p): ?><div class="zn z<?= $k ?>"><small><?= $k + 1 ?></small><em><?= e($p[0]) ?></em><span></span><span></span><span class="c"></span></div><?php endforeach; ?>
        <div class="curs"></div>
      </div>
    </div>
    <ul class="sps">
      <?php foreach ($pts as $k => $p): ?><li class="sp"><b><?= $k + 1 ?></b><div><h4><?= e($p[0]) ?></h4><p><?= e($p[1]) ?></p></div></li><?php endforeach; ?>
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
  <section class="slide var <?= $paysage ? 'land' : 'port' ?><?= $cle === 'ticket' ? ' tick' : '' ?> n<?= $n ?>" data-steps="<?= $n + 1 ?>"><div class="cadre">
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
$DS = [['actuel', 'Version actuelle', 'Vert et or (initial)', '#C89B2C'], ['d', 'Modèle 1', 'Vagues pétrole et orange', '#0A6B84'], ['e', 'Modèle 2', 'Cadre doré étoilé', '#B8862B'], ['f', 'Modèle 3', 'Losanges vert et orange', '#0B6A3A']];
$DC = [['actuel', 'Version actuelle', 'Vert (initial)', '#2E9A4C'], ['a', 'Proposition 1', 'Vagues vert et orange', '#3CB34A'], ['b', 'Proposition 2', 'Bande latérale vert profond', '#3F6B52'], ['c', 'Proposition 3', 'Rubans bleu pétrole', '#0A6B84']];
$AB = [['formateur', 'Badge', 'Formateur', '#0B8A4E'], ['media', 'Badge', 'Média', '#16A870'], ['stand', 'Badge', 'Stand', '#F77F00'], ['visiteur', 'Badge', 'Visiteur', '#0B8A4E']];
$TK = [['a', 'Ticket', 'N° JOS-0001-K7QM', '#0B8A4E'], ['b', 'Ticket', 'N° JOS-0002-R4XT', '#0B8A4E'], ['c', 'Ticket', 'N° JOS-0003-H9WE', '#F77F00'], ['d', 'Ticket', 'N° JOS-0004-P2ZN', '#F77F00'], ['e', 'Ticket', 'N° JOS-0001-X7K2', '#0B8A4E']];
$OF = [['1', 'Modèle 1', 'Cercles vert et orange', '#0B6A3A'], ['2', 'Modèle 2', 'Arcs concentriques pétrole', '#0A6B84'], ['3', 'Modèle 3', 'Triangles diagonaux', '#3CB34A'], ['4', 'Modèle 4', 'Demi-cercles en frise', '#F27B1B']];
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
/* ---------- transitions (6 modèles) ---------- */
#wipe{position:absolute;inset:0;z-index:40;pointer-events:none;overflow:hidden}
#wipe b{position:absolute;display:none;will-change:transform}
#wipe.go b{display:block}
/* t0 : bandes obliques */
#wipe.t0 b{top:-15%;bottom:-15%;left:-80%;width:75%;transform:skewX(-16deg);animation:wp 1.15s cubic-bezier(.75,0,.25,1) both}
#wipe.t0 b:nth-child(1){background:linear-gradient(90deg,#ff5e00,#ff9a3d)}
#wipe.t0 b:nth-child(2){background:linear-gradient(90deg,#0b6b7a,#12c2b0);animation-delay:.09s}
#wipe.t0 b:nth-child(3){background:#031a21;animation-delay:.18s}
#wipe.t0 b:nth-child(n+4){display:none}
@keyframes wp{from{transform:skewX(-16deg) translateX(0)}to{transform:skewX(-16deg) translateX(330%)}}
/* t1 : volets verticaux */
#wipe.t1 b{top:0;height:100%;width:12.6%;animation:vv 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t1 b:nth-child(odd){background:#031a21}#wipe.t1 b:nth-child(even){background:linear-gradient(180deg,#ff5e00,#ff9a3d)}
#wipe.t1 b:nth-child(1){left:0}#wipe.t1 b:nth-child(2){left:12.5%;animation-delay:.05s}#wipe.t1 b:nth-child(3){left:25%;animation-delay:.1s}#wipe.t1 b:nth-child(4){left:37.5%;animation-delay:.15s}
#wipe.t1 b:nth-child(5){left:50%;animation-delay:.2s}#wipe.t1 b:nth-child(6){left:62.5%;animation-delay:.25s}#wipe.t1 b:nth-child(7){left:75%;animation-delay:.3s}#wipe.t1 b:nth-child(8){left:87.5%;animation-delay:.35s}
@keyframes vv{0%{transform:translateY(-101%)}40%,55%{transform:translateY(0)}100%{transform:translateY(101%)}}
/* t2 : stores horizontaux */
#wipe.t2 b{left:0;width:100%;height:12.6%;animation:hh 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t2 b:nth-child(odd){background:#0b6b7a}#wipe.t2 b:nth-child(even){background:#031a21}
#wipe.t2 b:nth-child(1){top:0}#wipe.t2 b:nth-child(2){top:12.5%;animation-delay:.05s}#wipe.t2 b:nth-child(3){top:25%;animation-delay:.1s}#wipe.t2 b:nth-child(4){top:37.5%;animation-delay:.15s}
#wipe.t2 b:nth-child(5){top:50%;animation-delay:.2s}#wipe.t2 b:nth-child(6){top:62.5%;animation-delay:.25s}#wipe.t2 b:nth-child(7){top:75%;animation-delay:.3s}#wipe.t2 b:nth-child(8){top:87.5%;animation-delay:.35s}
@keyframes hh{0%{transform:translateX(-101%)}40%,55%{transform:translateX(0)}100%{transform:translateX(101%)}}
/* t3 : iris */
#wipe.t3 b:nth-child(1){inset:0;background:radial-gradient(circle,#0b6b7a 0,#031a21 70%);animation:ir 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t3 b:nth-child(2){left:50%;top:50%;width:140vmax;height:140vmax;margin:-70vmax 0 0 -70vmax;border-radius:50%;border:14px solid #ff7a1a;animation:ir2 1.2s cubic-bezier(.7,0,.3,1) both}
@keyframes ir{0%{clip-path:circle(0% at 50% 50%)}45%,60%{clip-path:circle(75% at 50% 50%)}100%{clip-path:circle(75% at 50% 50%);opacity:0}}
@keyframes ir2{0%{transform:scale(0);opacity:1}45%{transform:scale(1);opacity:1}60%,100%{transform:scale(1.15);opacity:0}}
/* t4 : flash + zoom */
#wipe.t4 b:nth-child(1){inset:0;background:radial-gradient(circle,#fff 0,#ffb46b 30%,#ff5e00 70%);animation:flsh 1s ease-in-out both}
#wipe.t4 b:nth-child(2){inset:0;background:#031a21;animation:flsh2 1s ease-in-out both}
@keyframes flsh{0%{opacity:0;transform:scale(.6)}45%{opacity:1;transform:scale(1.2)}100%{opacity:0;transform:scale(1.7)}}
@keyframes flsh2{0%{opacity:0}40%,55%{opacity:.9}100%{opacity:0}}
/* t5 : rideau en diagonale */
#wipe.t5 b{top:-60%;left:-10%;width:130%;height:34%;transform:rotate(-20deg) translateX(-130%);animation:dg 1.2s cubic-bezier(.7,0,.3,1) both}
#wipe.t5 b:nth-child(odd){background:#031a21}#wipe.t5 b:nth-child(even){background:linear-gradient(90deg,#ff5e00,#ff9a3d)}
#wipe.t5 b:nth-child(1){top:-30%}#wipe.t5 b:nth-child(2){top:-10%;animation-delay:.06s}#wipe.t5 b:nth-child(3){top:10%;animation-delay:.12s}#wipe.t5 b:nth-child(4){top:30%;animation-delay:.18s}
#wipe.t5 b:nth-child(5){top:50%;animation-delay:.24s}#wipe.t5 b:nth-child(6){top:70%;animation-delay:.3s}#wipe.t5 b:nth-child(7){top:90%;animation-delay:.36s}#wipe.t5 b:nth-child(8){display:none}
@keyframes dg{0%{transform:rotate(-20deg) translateX(-130%)}40%,55%{transform:rotate(-20deg) translateX(0)}100%{transform:rotate(-20deg) translateX(130%)}}
/* entrées de diapositive variées */
.slide.actif.e1 .cadre{animation:e1 1s cubic-bezier(.16,.9,.25,1) .45s both}
.slide.actif.e2 .cadre{animation:e2 1s cubic-bezier(.16,.9,.25,1) .45s both}
.slide.actif.e3 .cadre{animation:e3 1s cubic-bezier(.16,.9,.25,1) .45s both}
@keyframes e1{from{opacity:0;transform:scale(1.18);filter:blur(14px)}to{opacity:1;transform:none;filter:none}}
@keyframes e2{from{opacity:0;transform:perspective(1600px) rotateX(18deg) translateY(60px)}to{opacity:1;transform:none}}
@keyframes e3{from{opacity:0;transform:translateX(160px)}to{opacity:1;transform:none}}
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
/* tickets : grand format horizontal */
.tick.land .hero{left:90px;top:250px;width:1740px;height:590px}
.tick.land .panneau{left:90px;right:90px;top:868px;height:172px}
.tick .infos{position:absolute;left:0;top:0;width:760px;height:172px}
.tick .info h3{margin-top:12px;font-size:56px}
.tick .info small{font-size:26px;padding:6px 20px}
.tick .sw{margin-top:14px;height:14px}
.tick .vign{left:auto;right:0;bottom:0;width:1000px;flex-wrap:nowrap;gap:18px}
.tick.land .vg{flex:1 1 0}
.tick .vg img{border-width:3px}
.tick .vg span{display:none}
.tick .grille figcaption{display:none}
.tick.land.n4 .grille{left:90px;right:90px;top:290px;bottom:80px;gap:34px 40px}
.tick.land.n4 .grille figure img{width:840px;height:auto}
.tick.land.n5 .grille{left:90px;right:90px;top:270px;bottom:60px;gap:26px 30px}
.tick.land.n5 .grille figure img{width:560px;height:auto}

/* ---------- le site JOSPIA ---------- */
.site{--z:var(--o)}
.site.b{--z:var(--t)}.site.t{--z:#E8C25A}
.kz{display:inline-block;padding:10px 34px;border-radius:40px;background:var(--z);color:#031a21;font-size:30px;font-weight:800;letter-spacing:.3em;box-shadow:0 10px 34px rgba(0,0,0,.4),0 0 40px color-mix(in srgb,var(--z) 55%,transparent)}
.site .sh{position:absolute;left:90px;right:90px;top:222px;font-size:76px;line-height:1;font-weight:800;text-transform:uppercase;text-shadow:0 6px 30px rgba(0,0,0,.4)}
.site.arch .sh{font-size:92px}
.mock{position:absolute;left:90px;top:360px;width:880px;height:600px;border-radius:26px;background:#f4f8f8;overflow:hidden;box-shadow:0 40px 110px rgba(0,0,0,.65),0 0 0 3px rgba(255,255,255,.12),0 0 90px color-mix(in srgb,var(--z) 30%,transparent)}
.mock .bar{height:62px;background:#dfe8e9;display:flex;align-items:center;gap:10px;padding:0 20px}
.mock .bar i{width:16px;height:16px;border-radius:50%;background:#ff6b5f}.mock .bar i:nth-child(2){background:#ffbd2e}.mock .bar i:nth-child(3){background:#28c940}
.mock .url{margin-left:18px;flex:1;height:38px;border-radius:19px;background:#fff;display:flex;align-items:center;padding:0 22px;font-size:24px;font-weight:700;color:#12383f;overflow:hidden}
.mock .url span{display:inline-block;overflow:hidden;white-space:nowrap;width:0;border-right:3px solid var(--z)}
.slide.actif .mock .url span{animation:ty 1.6s 1.4s steps(var(--len)) both}
@keyframes ty{from{width:0}to{width:calc(var(--len)*1ch + 4px)}}
.corps{position:relative;height:538px;padding:22px 26px;display:grid;grid-template-columns:1fr 1fr;grid-auto-rows:1fr;gap:20px;background:linear-gradient(#f4f8f8,#e8f0f0)}
.nav{grid-column:1/3;height:44px;border-radius:12px;background:#0b3b46;display:flex;align-items:center;gap:20px;padding:0 20px}
.nav u{width:86px;height:12px;border-radius:6px;background:rgba(255,255,255,.35)}
.nav u.ac{background:var(--z);box-shadow:0 0 14px var(--z)}
.nav s{margin-left:auto;width:60px;height:24px;border-radius:12px;background:var(--o)}
.zn{position:relative;border-radius:18px;background:#fff;border:3px solid #d3e0e1;padding:44px 22px 18px;display:flex;flex-direction:column;gap:12px;opacity:.35;transform:scale(.97);transition:all .6s cubic-bezier(.2,.9,.2,1)}
.n3 .zn:nth-child(4){grid-column:1/3}
.n5 .zn:nth-child(6){grid-column:1/3}
.zn small{position:absolute;left:16px;top:12px;width:30px;height:30px;border-radius:9px;background:#b9c9cb;color:#fff;font-size:20px;font-weight:800;display:flex;align-items:center;justify-content:center}
.zn em{position:absolute;left:58px;top:12px;font-style:normal;font-size:24px;font-weight:800;color:#12383f}
.zn span{display:block;height:14px;border-radius:7px;background:linear-gradient(90deg,#d6e3e4 30%,#eef5f5 50%,#d6e3e4 70%);background-size:300% 100%}
.zn span.c{width:60%}
.zn.on{opacity:1;transform:none}
.zn.cur{border-color:var(--z);box-shadow:0 0 0 4px color-mix(in srgb,var(--z) 30%,transparent),0 18px 40px rgba(0,0,0,.25)}
.zn.cur small{background:var(--z);color:#031a21}
.zn.on span{animation:sh 2.4s linear infinite}
@keyframes sh{to{background-position:-300% 0}}
.curs{position:absolute;left:0;top:0;width:34px;height:34px;pointer-events:none;transition:transform .9s cubic-bezier(.3,.9,.2,1);transform:translate(420px,60px);filter:drop-shadow(0 6px 8px rgba(0,0,0,.4))}
.curs::before{content:"";position:absolute;inset:0;background:#031a21;clip-path:polygon(0 0,0 100%,28% 78%,46% 100%,60% 92%,44% 70%,82% 70%);border:0}
.curs::after{content:"";position:absolute;left:-14px;top:-14px;width:62px;height:62px;border-radius:50%;border:4px solid var(--z);opacity:0}
.curs.clic::after{animation:rip .8s .85s ease-out both}
@keyframes rip{from{opacity:1;transform:scale(.2)}to{opacity:0;transform:scale(1.4)}}
.sps{position:absolute;left:1030px;right:90px;top:360px;list-style:none;display:flex;flex-direction:column;gap:18px}
.sp{display:flex;gap:26px;align-items:center;padding:16px 26px;border-radius:22px;border:2px solid transparent;opacity:0;transform:translateX(120px);filter:blur(8px);transition:opacity .8s cubic-bezier(.2,.9,.2,1),transform .9s cubic-bezier(.2,.9,.2,1),filter .6s,background .5s,border-color .5s}
.sp b{flex:none;width:68px;height:68px;border-radius:20px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800}
.sp h4{font-size:44px;line-height:1.05;font-weight:800;color:#fff}
.sp p{margin-top:6px;font-size:30px;line-height:1.15;font-weight:600;color:var(--mu)}
.sp.on{opacity:.5;transform:none;filter:none}
.sp.cur{opacity:1;background:linear-gradient(90deg,color-mix(in srgb,var(--z) 22%,transparent),rgba(255,255,255,.03));border-color:var(--z)}
.sp.cur b{background:var(--z);color:#031a21;box-shadow:0 0 30px var(--z)}
.sp.cur h4{color:var(--z)}
.sp.cur p{color:#e3f1f1}
.n3 .sps{gap:30px}.n3 .sp{padding:24px 26px}
.n5 .sps{gap:10px}.n5 .sp{padding:10px 24px}
/* architecture */
.ar{position:absolute;left:90px;right:90px;top:380px;display:flex;align-items:center;justify-content:space-between}
.ac{width:540px;padding:56px 40px;border-radius:30px;background:linear-gradient(160deg,rgba(255,255,255,.12),rgba(255,255,255,.03));border:2px solid rgba(255,255,255,.16);box-shadow:0 24px 70px rgba(0,0,0,.4);opacity:0;transform:translateY(80px) scale(.92);transition:all .9s cubic-bezier(.2,.9,.2,1)}
.ac.on{opacity:1;transform:none}
.ac b{display:flex;width:76px;height:76px;border-radius:22px;align-items:center;justify-content:center;font-size:46px;font-weight:800;color:#031a21;background:var(--o);margin-bottom:22px;box-shadow:0 0 34px var(--o)}
.ac.b b{background:var(--t);box-shadow:0 0 34px var(--t)}.ac.t b{background:#E8C25A;box-shadow:0 0 34px #E8C25A}
.ac h3{font-size:56px;font-weight:800;line-height:1}
.ac p{margin-top:12px;font-size:36px;font-weight:700;color:var(--mu)}
.ac ul{margin-top:30px;list-style:none;display:flex;flex-direction:column;gap:20px}
.ac li{font-size:38px;font-weight:700;padding-left:34px;position:relative}
.ac li::before{content:"";position:absolute;left:0;top:14px;width:14px;height:14px;border-radius:4px;background:var(--o);transform:rotate(45deg)}
.fl{flex:1;height:10px;position:relative;display:flex;justify-content:space-around;align-items:center;opacity:0;transition:opacity .8s}
.fl::before{content:"";position:absolute;left:8px;right:8px;top:4px;height:2px;background:rgba(255,255,255,.25)}
.fl u{width:18px;height:18px;border-radius:50%;background:var(--o);box-shadow:0 0 18px var(--o);position:relative;animation:fw 1.6s linear infinite}
.fl u:nth-child(2){animation-delay:.5s}.fl u:nth-child(3){animation-delay:1s}
@keyframes fw{0%{transform:translateX(-60px);opacity:0}30%,70%{opacity:1}100%{transform:translateX(60px);opacity:0}}
.arch.fx .fl{opacity:1}
.bandeau-s{position:absolute;left:90px;right:90px;bottom:70px;text-align:center;font-size:44px;font-weight:800;color:var(--o2);letter-spacing:.04em;opacity:0;transition:opacity 1s}
.arch.fx .bandeau-s{opacity:1}

/* ---------- mouvement, éclats, explosions ---------- */
#geo{position:absolute;inset:0;z-index:-1;pointer-events:none;overflow:hidden}
#geo i{position:absolute;display:block;opacity:.5}
#geo .g1,#geo .g4,#geo .g7{width:90px;height:90px;border:4px solid var(--o);border-radius:14px;animation:gt 14s linear infinite}
#geo .g2,#geo .g5,#geo .g8{width:140px;height:140px;border:3px solid var(--t);border-radius:50%;animation:gf 11s ease-in-out infinite}
#geo .g3,#geo .g6,#geo .g9{width:26px;height:26px;background:var(--o);transform:rotate(45deg);box-shadow:0 0 30px var(--o);animation:gp 5s ease-in-out infinite}
#geo .g10{width:300px;height:300px;border:2px dashed rgba(18,194,176,.5);border-radius:50%;animation:gt 30s linear infinite}
#geo .g1{left:5%;top:70%}#geo .g2{left:88%;top:62%}#geo .g3{left:44%;top:90%}#geo .g4{left:92%;top:12%;animation-duration:19s}#geo .g5{left:2%;top:34%;animation-delay:-4s}
#geo .g6{left:70%;top:80%;animation-delay:-2s}#geo .g7{left:36%;top:4%;animation-duration:23s}#geo .g8{left:80%;top:40%;animation-delay:-7s}#geo .g9{left:18%;top:12%;animation-delay:-3s}#geo .g10{left:60%;top:-6%}
@keyframes gt{to{transform:rotate(360deg) translateY(-40px)}}
@keyframes gf{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-70px) scale(1.2)}}
@keyframes gp{0%,100%{transform:rotate(45deg) scale(1);opacity:.4}50%{transform:rotate(225deg) scale(1.8);opacity:1}}
/* titres : reflet lumineux qui passe */
.tete h2 .w i,.sh .w i,.intro h1 .w i{background-image:linear-gradient(100deg,#fff 40%,#ffd9b0 50%,#fff 60%);background-size:260% 100%;-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;animation:mot 1s cubic-bezier(.16,.95,.25,1) both,rf 4.5s 2s ease-in-out infinite !important;animation-delay:calc(.5s + var(--d)*.11s),calc(2s + var(--d)*.12s)}
.intro h1 .l2 .w i{-webkit-text-fill-color:transparent;background-image:none}
/* le reflet rend le texte transparent : l'ombre passe sur le parent */
.tete h2,.sh,.intro h1{text-shadow:none !important;filter:drop-shadow(0 6px 22px rgba(0,0,0,.45))}
@keyframes rf{0%{background-position:140% 0}55%,100%{background-position:-60% 0}}
/* éclats et chocs */
.shock{position:absolute;width:44px;height:44px;margin:-22px 0 0 -22px;border-radius:50%;border:7px solid var(--o);z-index:34;pointer-events:none;animation:shk 1.1s cubic-bezier(.1,.7,.3,1) forwards}
.shock.t{border-color:var(--t);animation-delay:.12s}
@keyframes shk{from{transform:scale(.2);opacity:1}to{transform:scale(16);opacity:0;border-width:1px}}
.pts li.cur i,.sp.cur b,.zn.cur small{animation:pul 1.6s ease-in-out infinite}
@keyframes pul{0%,100%{filter:brightness(1)}50%{filter:brightness(1.5) drop-shadow(0 0 16px var(--o))}}
.pts li.cur span,.sp.cur h4{animation:vib .9s cubic-bezier(.2,.9,.2,1)}
@keyframes vib{0%{transform:translateX(-30px) scale(.96);filter:blur(6px)}100%{transform:none;filter:none}}
.sp.cur{animation:cadre 2.2s ease-in-out infinite}
@keyframes cadre{0%,100%{box-shadow:0 0 0 0 color-mix(in srgb,var(--z) 0%,transparent)}50%{box-shadow:0 0 50px 4px color-mix(in srgb,var(--z) 45%,transparent)}}
.mock{animation:fm 7s ease-in-out infinite}
@keyframes fm{0%,100%{translate:0 0}50%{translate:0 -12px}}
.mock::after{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(110deg,transparent 40%,rgba(255,255,255,.55) 50%,transparent 60%);background-size:300% 100%;animation:ms 5s 2.5s ease-in-out infinite}
@keyframes ms{0%{background-position:130% 0}45%,100%{background-position:-50% 0}}
.var .hero::after{content:"";position:absolute;inset:0;border-radius:22px;pointer-events:none;background:linear-gradient(110deg,transparent 40%,rgba(255,255,255,.45) 50%,transparent 60%);background-size:300% 100%;animation:ms 4.5s 1.2s ease-in-out infinite}
.carte b,.num{animation:pul 2.4s ease-in-out infinite}

/* ---------- équipe ---------- */
.eq{position:absolute;left:90px;right:90px;top:340px;bottom:70px;display:flex;flex-wrap:wrap;justify-content:center;align-content:center;gap:34px 34px}
.mb{width:410px;height:350px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:18px;border-radius:28px;background:linear-gradient(160deg,rgba(255,255,255,.12),rgba(255,255,255,.03));border:2px solid rgba(255,255,255,.14);box-shadow:0 20px 60px rgba(0,0,0,.4);opacity:0;transform:translateY(90px) scale(.8) rotate(-3deg);filter:blur(8px);transition:all .9s cubic-bezier(.2,1.1,.3,1)}
.mb.on{opacity:.6;transform:none;filter:none}
.mb.cur{opacity:1;transform:scale(1.07);border-color:var(--o);background:linear-gradient(160deg,rgba(255,122,26,.25),rgba(255,255,255,.05));box-shadow:0 0 70px rgba(255,122,26,.5),0 24px 70px rgba(0,0,0,.5)}
.mb .ph{width:190px;height:190px;border-radius:50%;overflow:hidden;border:5px solid var(--t);background:linear-gradient(135deg,#0b6b7a,#031a21);display:flex;align-items:center;justify-content:center;box-shadow:0 0 30px rgba(18,194,176,.45)}
.mb.cur .ph{border-color:var(--o);animation:pul 1.6s ease-in-out infinite}
.mb .ph img{width:100%;height:100%;object-fit:cover}
.mb .ph span{font-size:76px;font-weight:800;color:#fff}
.mb h4{margin-top:18px;font-size:38px;line-height:1.05;font-weight:800;text-align:center;text-transform:uppercase}
.mb small{margin-top:10px;padding:4px 18px;border-radius:20px;background:rgba(255,255,255,.14);font-size:20px;font-weight:800;letter-spacing:.22em;color:var(--mu)}
.mb.rs small{background:var(--o);color:#031a21}

/* ---------- fiche profil ---------- */
.pfo{position:absolute;left:150px;top:250px;width:560px;height:730px}
.pfi{position:absolute;inset:0;border-radius:44px;overflow:hidden;border:6px solid var(--o);box-shadow:0 0 0 12px rgba(255,122,26,.15),0 0 90px rgba(255,122,26,.5),0 40px 100px rgba(0,0,0,.6);animation:fm 7s ease-in-out infinite}
.pfi img{width:100%;height:100%;object-fit:cover;object-position:50% 30%}
.pfi::after{content:"";position:absolute;inset:0;background:linear-gradient(110deg,transparent 40%,rgba(255,255,255,.5) 50%,transparent 60%);background-size:300% 100%;animation:ms 5s 2s ease-in-out infinite}
.pfo i{position:absolute;border-radius:50%;pointer-events:none}
.pfo .r1{left:-70px;top:-70px;right:-70px;bottom:-70px;border:3px dashed rgba(18,194,176,.6);animation:gt 26s linear infinite;border-radius:70px}
.pfo .r2{left:-40px;top:-40px;right:-40px;bottom:-40px;border:3px solid rgba(255,122,26,.4);border-radius:60px;animation:gt 40s linear infinite reverse}
.pfn{position:absolute;left:850px;right:90px;top:270px;font-size:118px;line-height:.98;font-weight:800;text-transform:uppercase;text-shadow:none !important}
.prs{position:absolute;left:850px;right:70px;top:440px;list-style:none;display:flex;flex-direction:column;gap:20px}
.pr{padding:18px 34px;border-radius:24px;border:2px solid transparent;opacity:0;transform:translateX(140px) scale(.95);filter:blur(8px);transition:all .9s cubic-bezier(.2,.9,.2,1)}
.pr small{display:block;font-size:26px;font-weight:800;letter-spacing:.26em;color:var(--o2)}
.pr strong{display:block;margin-top:6px;font-size:44px;line-height:1.08;font-weight:800}
.pr.on{opacity:.55;transform:none;filter:none}
.pr.cur{opacity:1;background:linear-gradient(90deg,rgba(255,122,26,.25),rgba(255,255,255,.03));border-color:var(--o);box-shadow:0 0 60px rgba(255,122,26,.35)}
.prof.lg .pfn{font-size:88px;top:262px}
.prof.lg .prs{top:500px}

/* ---------- annonce des membres ---------- */
.ann{--z:var(--o)}
.ann .cadre{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding-top:40px}
.ann .kz{margin-bottom:6px}
.bign b{display:block;font-size:360px;line-height:1;font-weight:800;background:linear-gradient(180deg,#fff 20%,#ffb46b 60%,#ff7a1a);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;filter:drop-shadow(0 0 60px rgba(255,122,26,.55))}
.annt{margin-top:-10px;font-size:68px;line-height:1.05;font-weight:800;max-width:1500px;text-transform:uppercase;filter:drop-shadow(0 6px 22px rgba(0,0,0,.45))}
.anns{margin-top:26px;font-size:40px;font-weight:600;color:var(--o2)}
.avs{margin-top:44px;display:flex;gap:30px;justify-content:center}
.av{width:150px;height:150px;border-radius:50%;overflow:hidden;border:5px solid var(--o);box-shadow:0 0 36px rgba(255,122,26,.55);opacity:0;transform:scale(0) rotate(-40deg)}
.av img{width:100%;height:100%;object-fit:cover;object-position:50% 22%}
.slide.actif .av{animation:avp .9s cubic-bezier(.2,1.3,.3,1) both,fm 5s 2s ease-in-out infinite;animation-delay:calc(3.2s + var(--k)*.28s),calc(4s + var(--k)*.3s)}
@keyframes avp{to{opacity:1;transform:none}}
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
  <div class="bg"></div><div class="grid"></div><div class="orb o1"></div><div class="orb o2"></div><div id="geo"><i class="g1"></i><i class="g2"></i><i class="g3"></i><i class="g4"></i><i class="g5"></i><i class="g6"></i><i class="g7"></i><i class="g8"></i><i class="g9"></i><i class="g10"></i></div><canvas id="ptc" width="1920" height="1080"></canvas><div class="balayage"></div>
  <div class="logo"><img src="<?= BASE_URL ?>/assets/img/pres_bandeau.png" alt="AEEMCI — Secrétariat Régional Abidjan Nord — Sous-comités Anyama 1 &amp; 2 — JOSPIA"></div>
  <div class="tag">Bataille de Kirina</div>

  <section class="slide intro" data-steps="1"><div class="cadre">
    <div class="k a" style="--d:0">Bataille de Kirina</div>
    <h1><?= mots('COMMISSION', 1) ?><span class="l2"><?= mots('ADMINISTRATION', 3) ?></span></h1>
    <div class="ss a" style="--d:9">Réalisations, travaux non effectués et difficultés</div>
    <div class="ligne"></div>
  </div></section>

  <?php
  if ($membresAdmin) { afficherAnnonceMembres($membresAdmin); afficherProfils($membresAdmin); }
  afficherVariantes('RÉALISATIONS : LE BADGE DES SÉMINARISTES', 'badges', 'seminariste', $BA);
  afficherVariantes('RÉALISATIONS : LE BADGE DES MEMBRES DE COMMISSION', 'badges', 'commission', $BA);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES MEMBRES DE COMMISSION', 'diplomes', 'commission', $DC, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DU SÉMINARISTE', 'diplomes', 'seminariste', $DS, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES OFFICIELS', 'diplomes', 'officiel', $OF, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LES AUTRES BADGES', 'badges', 'autre', $AB, 'jpg');
  afficherVariantes('RÉALISATIONS : LES TICKETS', 'tickets', 'ticket', $TK, 'jpg', true);
  afficherSiteArchi();
  foreach ($SITE as [$z, $t, $u, $pp]) { afficherSite($z, $t, $u, $pp); }
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
  <div id="wipe"><b></b><b></b><b></b><b></b><b></b><b></b><b></b><b></b></div>
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

  var sl=[].slice.call(document.querySelectorAll('.slide')),n=sl.length,c=-1,st=0,busy=false,dC=-2,dS=-1;
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
    if(s.classList.contains('equipe')||s.classList.contains('prof')){[].forEach.call(s.querySelectorAll('.mb,.pr'),function(o,i){o.classList.toggle('on',st>=i+1);o.classList.toggle('cur',st===i+1)})}
    else if(s.classList.contains('site')){
      if(s.classList.contains('arch')){
        [].forEach.call(s.querySelectorAll('.ac'),function(o,i){o.classList.toggle('on',st>=i+1)});
        s.classList.toggle('fx',st>=4);
      }else{
        [].forEach.call(s.querySelectorAll('.sp'),function(o,i){o.classList.toggle('on',st>=i+1);o.classList.toggle('cur',st===i+1)});
        var zs=s.querySelectorAll('.zn');
        [].forEach.call(zs,function(o,i){o.classList.toggle('on',st>=i+1);o.classList.toggle('cur',st===i+1)});
        var cu=s.querySelector('.curs'),z=zs[Math.max(0,st-1)];
        if(cu&&z){cu.style.transform='translate('+(z.offsetLeft+z.offsetWidth*.62)+'px,'+(z.offsetTop+z.offsetHeight*.62)+'px)';cu.classList.remove('clic');void cu.offsetWidth;if(st>0)cu.classList.add('clic')}
      }
    }
    if(s.classList.contains('liste')){
      var li=s.querySelectorAll('li');
      [].forEach.call(li,function(o,i){o.classList.toggle('on',st>=i+1);o.classList.toggle('cur',st===i+1)});
    }
    if(st>0&&(c!==dC||st!==dS)){var cur=s.querySelector('.pts li.cur,.sp.cur,.mb.cur,.pr.cur');if(cur)exploser(cur,true);else if(s.classList.contains('var')&&st<s.querySelectorAll('.hv').length)exploser(s.querySelector('.hero'),false)}
    dC=c;dS=st;
    document.getElementById('prec').disabled=(c===0&&st===0);
    document.getElementById('suiv').disabled=(c===n-1&&st>=ns-1);
  }
  function go(i,step,direct){
    if(i<0||i>=n)return;
    if(i===c){st=step;maj();return}
    function bascule(){
      sl.forEach(function(s,k){s.classList.toggle('actif',k===i);s.classList.remove('e1','e2','e3');if(k===i&&i%4)s.classList.add('e'+(i%4))});
      c=i;st=step;maj();
      cpt.textContent=(i+1)+' / '+n;prog.style.width=((i+1)/n*100)+'%';
      try{history.replaceState(null,'','#'+(i+1))}catch(e){}
      compter(sl[i]);
      setTimeout(function(){boom(960,300,70);choc(960,300)},900);
      if(sl[i].classList.contains('ann')){[].forEach.call(sl[i].querySelectorAll('.av'),function(a,k){setTimeout(function(){exploser(a,false)},3300+k*280)});setTimeout(function(){boom(960,420,120);choc(960,420)},1900)}
      if(sl[i].classList.contains('fin'))setTimeout(fete,1100);
    }
    if(direct||c<0){bascule();return}
    var T=i%6;busy=true;wipe.className='';void wipe.offsetWidth;wipe.className='go t'+T;
    setTimeout(bascule,T===4?420:560);setTimeout(function(){wipe.className='';busy=false},1500);
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
  var stg=document.getElementById('stage');
  function boom(x,y,n){for(var k=0;k<n;k++){var a=Math.random()*6.283,v=3+Math.random()*10;F.push({x:x,y:y,vx:Math.cos(a)*v,vy:Math.sin(a)*v-2,l:1,c:col[k%4]})}if(!run){run=true;bo()}}
  function choc(x,y){[0,1].forEach(function(t){var d=document.createElement('div');d.className='shock'+(t?' t':'');d.style.left=x+'px';d.style.top=y+'px';stg.appendChild(d);setTimeout(function(){d.remove()},1400)})}
  function exploser(el,gauche){
    if(!el)return;var r=el.getBoundingClientRect(),sr=stg.getBoundingClientRect(),k=sr.width/1920||1;
    var x=gauche?(r.left-sr.left)/k+50:(r.left-sr.left+r.width/2)/k,y=(r.top-sr.top+r.height/2)/k;
    boom(x,y,55);choc(x,y);
  }
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
