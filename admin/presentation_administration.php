<?php
require_once __DIR__ . '/../includes/init.php';
if (!defined('PRESENTATION_PUBLIQUE')) { exigerRole(['admin', 'superadmin']); }
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
function afficherListe($num, $nom, $res, $gras, $cl = '') { ?>
  <section class="slide liste<?= $cl ? ' ' . $cl : '' ?>" data-steps="<?= count($res) + 1 ?>"><div class="cadre">
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
   ["Compte à rebours", "Décompte jusqu'à l'ouverture"],
   ["3 étapes", "S'inscrire, recevoir ses identifiants, consulter son espace"],
   ["Accès direct", "Formulaires séminariste, commission et visiteurs"]]],
 ['FRONT-END', "L'INSCRIPTION DES SÉMINARISTES", '/inscription_seminariste', [
   ["Formulaire", "Rempli en ligne par le séminariste"],
   ["Matricule", "JOS-001, JOS-002… attribué automatiquement"],
   ["Dortoir", "Hommes, Femmes ou Pépinière, affecté automatiquement"],
   ["Compte", "Créé automatiquement, identifiants après paiement"]]],
 ['FRONT-END', "L'INSCRIPTION DES COMMISSIONS", '/inscription_commission', [
   ["Commission", "Choix parmi les 13 commissions"],
   ["Badge", "Généré automatiquement"],
   ["Diplôme", "Généré automatiquement"],
   ["Compte", "Identifiants créés automatiquement"]]],
 ['FRONT-END', "LE PAIEMENT ET SON SUIVI", '/paiement  ·  /statut', [
   ["Paiement", "Frais remis en espèces à la commission Finance"],
   ["Reçu", "Avec QR code unique"],
   ["Suivi", "Statut du paiement consultable en ligne"],
   ["Validation", "Confirmée par la commission Finance"]]],
 ['FRONT-END', "LE TEST D'ENTRÉE", '/quiz', [
   ["Test en ligne", "Composé par le séminariste après son inscription"],
   ["Minuteur", "Soumission automatique à la fin du temps"],
   ["Note sur 20", "Calculée automatiquement"],
   ["Niveau et dortoir", "Déterminés d'après la note"],
   ["Correction", "Consultable question par question"]]],
 ['FRONT-END', "LES VISITEURS", '/visiteur', [
   ["Arrivée", "Le visiteur enregistre son arrivée et son motif"],
   ["Sortie", "Validée par la commission Sécurité"],
   ["Suivi", "Liste réservée à la commission Sécurité"]]],
 ['FRONT-END', "L'ESPACE PERSONNEL", '/espace', [
   ["Fiche", "D'inscription, téléchargeable en PDF"],
   ["Badge", "Aperçu dans l'espace après paiement validé"],
   ["Diplôme", "Aperçu dans l'espace après paiement validé"],
   ["Bulletin", "Notes et niveau du séminariste"]]],
 ['FRONT-END', "BADGES ET DIPLÔMES AUTOMATIQUES", '/espace', [
   ["Badge séminariste", "Généré automatiquement"],
   ["Diplôme séminariste", "Généré automatiquement"],
   ["Badge membre", "Généré automatiquement"],
   ["Diplôme membre", "Généré automatiquement, membre de commission"],
   ["Impression", "Téléchargement et impression par les administrateurs"]]],
 ['FRONT-END', "LA DEMANDE DE SORTIE DU CAMP", '/espace/sortie', [
   ["1. La demande", "Motif, heure de sortie et heure de retour"],
   ["2. MG ou MGA", "Accepte ou refuse la demande"],
   ["3. Sécurité", "Confirme la sortie par le bouton OK"],
   ["4. Le retour", "La personne signale son retour sur le camp"]]],
 ['FRONT-END', "LES RAPPORTS JOURNALIERS", '/espace/rapport', [
   ["Qui", "Le responsable de chaque commission"],
   ["Contenu", "Activités, difficultés et prévisions du jour"],
   ["Gestion", "Modifier ou supprimer ses propres rapports"],
   ["Lecture", "Rapports consultables par le Comité managérial"]]],
 ['FRONT-END', "LA CONNEXION ET LE COMPTE", '/login  ·  /compte', [
   ["Identifiants", "Créés automatiquement à l'inscription"],
   ["Rôles", "Chaque compte accède à ses pages"],
   ["Sécurité", "Mots de passe chiffrés"],
   ["Compte", "Changement du mot de passe"]]],
 ['BACK-END', "LE TABLEAU DE BORD", '/admin/dashboard', [
   ["Finances", "Statistiques des paiements en espèces"],
   ["Commissions", "Répartition par commission"],
   ["Sous-comités et sections", "Répartition des séminaristes"],
   ["Niveaux", "Répartition selon le test d'entrée"]]],
 ['BACK-END', "L'ADMINISTRATION", '/admin/administration', [
   ["Inscrits", "Consulter et modifier séminaristes et membres"],
   ["Dortoirs", "Listes par dortoir et par niveau"],
   ["Documents", "Badges et diplômes : aperçu et impression"],
   ["Comptes", "Comptes admin et paramètres du site"]]],
 ['BACK-END', "LA COMMISSION SCIENTIFIQUE", '/admin/commission_scientifique', [
   ["Notes", "Saisie des notes"],
   ["Bulletins", "Consultation et impression"],
   ["Test d'entrée", "Questions, matières et niveaux selon les notes"],
   ["Programme", "Programme journalier et archives"]]],
 ['BACK-END', "LA COMMISSION FINANCE", '/finance/paiements', [
   ["Paiements", "Gestion des paiements en espèces"],
   ["Scanner", "Vérification d'un reçu par son QR code"],
   ["Tickets", "Gestion et impression des tickets"]]],
 ['BACK-END', "LA COMMISSION SÉCURITÉ", '/securite', [
   ["Visiteurs", "Arrivées et sorties des visiteurs"],
   ["Sorties du camp", "Confirmation des sorties acceptées par le MG / MGA"],
   ["Retours", "Suivi des personnes sorties et rentrées"]]],
 ['BACK-END', "LE COMITÉ MANAGÉRIAL", '/admin/comite_manageriale', [
   ["Rapports", "Lecture des rapports journaliers des commissions"],
   ["Sorties", "Le MG / MGA accepte ou refuse les demandes de sortie"],
   ["Rôles", "Le super administrateur attribue le MG / MGA"]]],
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

function afficherChapitre($num, $titre, $sous, $style) {
    $pts = [];
    for ($i = 0; $i < 32; $i++) { $a = $i * M_PI / 16 - M_PI / 2; $r = ($i % 2 === 0) ? 50 : 33; $pts[] = round(50 + $r * cos($a), 2) . '% ' . round(50 + $r * sin($a), 2) . '%'; }
    $poly = implode(',', $pts); ?>
  <section class="slide chap <?= $style ?>"><div class="cadre">
    <?php if ($style === 'etoile'): ?>
    <div class="stars"><i class="s1" style="clip-path:polygon(<?= $poly ?>)"></i><i class="s2" style="clip-path:polygon(<?= $poly ?>)"></i><i class="s3" style="clip-path:polygon(<?= $poly ?>)"></i>
      <?php for ($k = 0; $k < 12; $k++): ?><u style="--k:<?= $k ?>;left:<?= 6 + (($k * 37) % 88) ?>%;top:<?= 8 + (($k * 53) % 80) ?>%"></u><?php endfor; ?></div>
    <?php else: ?>
    <div class="spot"></div>
    <div class="rid"><b class="g"></b><b class="d"></b><em></em></div>
    <?php endif; ?>
    <div class="ccn">
      <div class="cn a" style="--d:<?= $style === 'etoile' ? 1 : 7 ?>">RÉALISATION <?= e($num) ?></div>
      <h2 class="ct"><?= mots($titre, $style === 'etoile' ? 3 : 9) ?></h2>
      <div class="cl a" style="--d:<?= $style === 'etoile' ? 8 : 15 ?>"></div>
      <p class="cs a" style="--d:<?= $style === 'etoile' ? 9 : 16 ?>"><?= e($sous) ?></p>
    </div>
  </div></section>
<?php }

function afficherAnnonceMembres($membres) {
    $f = array_values(array_filter($membres, function ($m) { return !empty($m['fiche']); })); $n = count($f);
    if (!$n) return; ?>
  <section class="slide ann"><div class="cadre">
    <div class="kz a" style="--d:0">NOTRE ÉQUIPE</div>
    <div class="bign a" style="--d:2"><b data-n="<?= $n ?>">0</b></div>
    <h2 class="annt"><?= mots($n > 1 ? "MEMBRES DANS LA COMMISSION ADMINISTRATION" : "MEMBRE DANS LA COMMISSION ADMINISTRATION", 4) ?></h2>
    <p class="anns a" style="--d:9">Une équipe constituée et engagée au service des JOSPIA</p>
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
  <section class="slide site arch" data-steps="4"><div class="cadre">
    <header class="tete"><div class="kz a" style="--d:0">LE SITE JOSPIA</div></header>
    <h2 class="sh"><?= mots("RÉALISATIONS : LE SITE JOSPIA", 1) ?></h2>
    <div class="ar">
      <?php foreach ([
        ['FRONT-END', 'Ce que voit l\'utilisateur', ['Pages et formulaires', 'Espace personnel', 'Demandes de sortie et rapports'], 'f'],
        ['BACK-END', 'Le cerveau et la base de données', ['Règles, rôles et sécurité', 'PDF, badges, QR codes', 'Base de données du site'], 'b'],
      ] as $i => [$t, $d, $l, $c]): ?>
      <div class="ac <?= $c ?>"><b><?= $i + 1 ?></b><h3><?= e($t) ?></h3><p><?= e($d) ?></p><ul><?php foreach ($l as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
      <?php if ($i < 1): ?><div class="fl"><u></u><u></u><u></u></div><?php endif; ?>
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
<meta name="viewport" id="vp" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
<title>Commission Administration — Bataille de Kirina — JOSPIA 2026</title>
<style>
@font-face{font-family:'Barlow';font-weight:800;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-latin-800-normal.woff2) format('woff2')}
@font-face{font-family:'Barlow';font-weight:700;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-latin-700-normal.woff2) format('woff2')}
@font-face{font-family:'BarlowSC';font-weight:600;font-display:swap;src:url(<?= BASE_URL ?>/assets/fonts/barlow-semi-condensed-latin-600-normal.woff2) format('woff2')}
:root{--s:1;--o:#FF7A1A;--o2:#FFB36B;--t:#12C2B0;--bg:#03141A;--bg2:#062F3A;--tx:#F4FAF9;--mu:#9DB9BD}
*{box-sizing:border-box;margin:0;padding:0}
html,body{width:100%;height:100%;overflow:hidden;background:radial-gradient(120% 90% at 50% 40%,#06303a 0,#010a0d 75%);-webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;color:var(--tx);font-family:'Barlow','Inter',system-ui,Arial,sans-serif;overscroll-behavior:none;touch-action:manipulation;-webkit-tap-highlight-color:transparent}
button{font-family:inherit}
#stage{position:absolute;left:50%;top:50%;width:var(--W,1920px);height:var(--H,1080px);margin:calc(var(--H,1080px) / -2) 0 0 calc(var(--W,1920px) / -2);transform:rotate(var(--r,0deg)) scale(var(--s));transform-origin:center;overflow:hidden;background:var(--bg);isolation:isolate;contain:layout paint}
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
.dense2 .pts{top:270px;gap:16px}.dense2 .pts li{font-size:38px}.dense .pts{top:290px;gap:20px}.dense .pts li{font-size:41px}.dense .pts li i{width:20px;height:20px;margin-top:13px}
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
.ac{width:800px;padding:56px 40px;border-radius:30px;background:linear-gradient(160deg,rgba(255,255,255,.12),rgba(255,255,255,.03));border:2px solid rgba(255,255,255,.16);box-shadow:0 24px 70px rgba(0,0,0,.4);opacity:0;transform:translateY(80px) scale(.92);transition:all .9s cubic-bezier(.2,.9,.2,1)}
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

/* ---------- pages d'annonce (rideau / étoile) ---------- */
.chap .cadre{display:flex;align-items:center;justify-content:center;padding:0}
.ccn{position:relative;z-index:3;text-align:center;display:flex;flex-direction:column;align-items:center}
.cn{padding:12px 44px;border-radius:40px;background:var(--o);color:#031a21;font-size:34px;font-weight:800;letter-spacing:.34em;box-shadow:0 0 50px rgba(255,122,26,.6)}
.ct{margin-top:34px;font-size:230px;line-height:.95;font-weight:800;text-transform:uppercase;max-width:1700px;filter:drop-shadow(0 10px 40px rgba(0,0,0,.6))}
.cl{margin-top:34px;width:560px;height:8px;border-radius:4px;background:linear-gradient(90deg,var(--t),var(--o))}
.cs{margin-top:30px;font-size:46px;font-weight:700;color:#ffc58f;max-width:1500px;text-shadow:0 3px 18px rgba(0,0,0,.8)}
/* rideau */
.spot{position:absolute;left:50%;top:-10%;width:1300px;height:1300px;margin-left:-650px;background:radial-gradient(ellipse at 50% 0,rgba(255,200,140,.35),transparent 60%);clip-path:polygon(40% 0,60% 0,100% 100%,0 100%);opacity:0}
.slide.actif .spot{animation:spt 2s 1.5s ease-out both}
@keyframes spt{to{opacity:1}}
.rid{position:absolute;inset:0;z-index:6;pointer-events:none;overflow:hidden}
.rid b{position:absolute;top:0;bottom:0;width:51%;background:repeating-linear-gradient(90deg,#0d7d8e 0 64px,#08515f 64px 128px,#0b6b7a 128px 192px);box-shadow:inset 0 0 120px rgba(0,0,0,.55)}
.rid b::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(255,255,255,.18),transparent 25%,rgba(0,0,0,.35));}
.rid b.g{left:0;border-right:10px solid var(--o2)}.rid b.d{right:0;border-left:10px solid var(--o2)}
.rid em{position:absolute;left:0;right:0;top:0;height:90px;background:radial-gradient(circle at 50% 100%,transparent 44px,#ff7a1a 46px) 0 0/120px 90px repeat-x,#ff7a1a;background:linear-gradient(#ff7a1a,#ff7a1a) 0 0/100% 60px no-repeat,radial-gradient(circle at 50% 0,#ff7a1a 58px,transparent 60px) 0 50px/120px 50px repeat-x;filter:drop-shadow(0 12px 18px rgba(0,0,0,.5))}
.slide.actif .rid b.g{animation:rg 1.6s .55s cubic-bezier(.7,0,.2,1) both}
.slide.actif .rid b.d{animation:rd 1.6s .55s cubic-bezier(.7,0,.2,1) both}
.slide.actif .rid em{animation:rv 1s 2.1s ease-in both}
@keyframes rg{to{transform:translateX(-105%)}}@keyframes rd{to{transform:translateX(105%)}}
@keyframes rv{to{transform:translateY(-130%)}}
/* étoile */
.stars{position:absolute;inset:0;z-index:1;pointer-events:none;overflow:hidden}
.stars i{position:absolute;left:50%;top:50%;border-radius:0;transform:translate(-50%,-50%) scale(0) rotate(0)}
.s1{width:1900px;height:1900px;background:conic-gradient(from 0deg,rgba(255,122,26,.32),rgba(18,194,176,.28),rgba(255,122,26,.32))}
.s2{width:1250px;height:1250px;background:conic-gradient(from 90deg,rgba(18,194,176,.4),rgba(255,140,60,.3),rgba(18,194,176,.4))}
.s3{width:760px;height:760px;background:radial-gradient(circle,rgba(255,255,255,.22),rgba(255,122,26,.25) 60%,transparent 70%)}
.slide.actif .s1{animation:sa 2.2s .5s cubic-bezier(.2,.9,.2,1) both,sr 40s 2.7s linear infinite}
.slide.actif .s2{animation:sa 2s .8s cubic-bezier(.2,.9,.2,1) both,sr 28s 2.8s linear infinite reverse}
.slide.actif .s3{animation:sa 1.6s 1.1s cubic-bezier(.2,.9,.2,1) both,pul 3s 2.7s ease-in-out infinite}
@keyframes sa{from{transform:translate(-50%,-50%) scale(0) rotate(-180deg);opacity:0}to{transform:translate(-50%,-50%) scale(1) rotate(0);opacity:1}}
@keyframes sr{to{transform:translate(-50%,-50%) rotate(360deg)}}
.stars u{position:absolute;width:34px;height:34px;background:#fff;clip-path:polygon(50% 0,60% 40%,100% 50%,60% 60%,50% 100%,40% 60%,0 50%,40% 40%);opacity:0;filter:drop-shadow(0 0 10px #fff)}
.slide.actif .stars u{animation:tw 2.4s infinite;animation-delay:calc(1.5s + var(--k)*.25s)}
@keyframes tw{0%,100%{opacity:0;transform:scale(.3) rotate(0)}50%{opacity:1;transform:scale(1.6) rotate(90deg)}}
/* contrôles */
.cmd{position:fixed;left:0;right:0;bottom:0;z-index:60;display:flex;justify-content:space-between;align-items:center;padding:14px 22px;opacity:0;transition:opacity .5s;pointer-events:none}
body.actifs .cmd{opacity:1;pointer-events:auto}
.cmd .g{display:flex;gap:10px;align-items:center}
.cmd button,.cmd a{width:52px;height:52px;border-radius:50%;border:0;background:rgba(255,255,255,.14);backdrop-filter:blur(8px);color:#fff;font-size:22px;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;transition:.2s}
.cmd button:hover,.cmd a:hover{background:var(--o);transform:scale(1.1)}
.cmd button:disabled{opacity:.3;pointer-events:none}
.cmd .cp{min-width:70px;text-align:right;font-weight:800;color:#cfe7e8;font-size:18px}
body.cache{cursor:none}

/* =====================================================================
   COUCHE v3 : design system, profondeur, mouvement, responsive
   ===================================================================== */
@property --ang{syntax:'<angle>';inherits:false;initial-value:0deg}
:root{
  --ease-out:cubic-bezier(.16,.95,.25,1);--ease-spring:cubic-bezier(.3,1.45,.4,1);--ease-io:cubic-bezier(.7,0,.3,1);
  --glass:linear-gradient(155deg,rgba(255,255,255,.16),rgba(255,255,255,.04));
  --edge:rgba(255,255,255,.18);--shadow-1:0 14px 40px rgba(0,0,0,.35);--shadow-2:0 30px 90px rgba(0,0,0,.55);
  --glow:color-mix(in srgb,var(--z,var(--o)) 45%,transparent);
}
.cadre{perspective:1800px}
/* profondeur : vignette + grain + projecteur qui suit le pointeur */
#stage::after{content:"";position:absolute;inset:0;z-index:5;pointer-events:none;background:radial-gradient(ellipse at 50% 45%,transparent 58%,rgba(0,0,0,.5) 100%)}
#spot{position:absolute;left:0;top:0;width:900px;height:900px;margin:-450px 0 0 -450px;z-index:-1;pointer-events:none;border-radius:50%;background:radial-gradient(circle,rgba(255,170,90,.22),rgba(18,194,176,.10) 45%,transparent 70%);transform:translate(960px,540px);transition:transform .35s ease-out;mix-blend-mode:screen}
/* comètes */
.cm{position:absolute;left:0;top:0;width:340px;height:3px;border-radius:3px;z-index:-1;pointer-events:none;opacity:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.95));filter:drop-shadow(0 0 10px #fff);animation:cmt 9s linear infinite}
.cm.c2{animation-delay:-3s;animation-duration:11s;width:260px;background:linear-gradient(90deg,transparent,#ffb36b)}
.cm.c3{animation-delay:-6s;animation-duration:13s;background:linear-gradient(90deg,transparent,#5ff0df)}
@keyframes cmt{0%{opacity:0;transform:translate(-400px,140px) rotate(24deg)}6%{opacity:1}30%{opacity:0;transform:translate(1500px,820px) rotate(24deg)}100%{opacity:0;transform:translate(1500px,820px) rotate(24deg)}}
/* cartes verre : relief, flottement, bordure lumineuse tournante */
.ac{background:var(--glass);backdrop-filter:blur(6px);border-color:var(--edge);box-shadow:var(--shadow-2),inset 0 1px 0 rgba(255,255,255,.25);rotate:var(--tilt,0 0 1 0deg);transition:opacity .9s var(--ease-out),transform .9s var(--ease-spring),rotate .25s ease-out}
.ac.on{animation:fy 7s ease-in-out infinite}
.ac.b.on{animation-delay:-3s}
@keyframes fy{50%{translate:0 -14px}}
.ac.on::after,.sp.cur::after{content:"";position:absolute;inset:-2px;border-radius:inherit;padding:3px;pointer-events:none;background:conic-gradient(from var(--ang),transparent 0 68%,var(--z,var(--o)) 84%,#fff 92%,transparent 100%);-webkit-mask:linear-gradient(#000 0 0) content-box,linear-gradient(#000 0 0);-webkit-mask-composite:xor;mask-composite:exclude;animation:ang 3.2s linear infinite}
.ac{position:relative;--z:var(--o)}.ac.b{--z:var(--t)}
.sp{position:relative}
@keyframes ang{to{--ang:360deg}}
.ac li{transition:transform .5s var(--ease-spring)}
.ac.on li{animation:li2 .8s var(--ease-spring) both}
.ac.on li:nth-child(2){animation-delay:.12s}.ac.on li:nth-child(3){animation-delay:.24s}
@keyframes li2{from{opacity:0;transform:translateX(-40px)}}
/* fenêtre de navigateur : scan, flottement, inclinaison 3D */
.slide.actif .mock.a{animation:ap 1s var(--ease-out) both,fyy 8s 2.6s ease-in-out infinite;animation-delay:calc(.6s + var(--d,0)*.12s),2.6s}
@keyframes fyy{50%{translate:0 -10px}}
.mock{rotate:var(--tilt,0 0 1 0deg);transition:rotate .25s ease-out}
.mock::after{content:"";position:absolute;left:0;right:0;top:-160px;height:160px;pointer-events:none;background:linear-gradient(transparent,rgba(18,194,176,.28),rgba(255,255,255,.5),transparent);animation:scan 4.5s 2.2s linear infinite}
@keyframes scan{to{top:105%}}
/* zones et points : ressort + halo */
.zn{transition:opacity .6s var(--ease-out),transform .8s var(--ease-spring),border-color .4s,box-shadow .5s}
.zn.on{box-shadow:0 10px 26px rgba(8,40,48,.12)}
.zn.cur{animation:zp 1.8s ease-in-out infinite}
@keyframes zp{50%{box-shadow:0 0 0 9px color-mix(in srgb,var(--z) 14%,transparent),0 18px 40px rgba(0,0,0,.25)}}
.sp{transition:opacity .8s var(--ease-out),transform .9s var(--ease-spring),filter .6s,background .5s,border-color .5s}
.sp.cur{box-shadow:0 18px 50px rgba(0,0,0,.35),0 0 60px var(--glow);transform:translateX(-14px) scale(1.03)}
.sp.cur b{animation:pb 1.6s ease-in-out infinite}
@keyframes pb{50%{transform:scale(1.14) rotate(-4deg)}}
.sp h4{letter-spacing:.005em}
/* titres, pastilles */
.kz{position:relative;overflow:hidden}
.kz::after{content:"";position:absolute;top:0;bottom:0;width:80px;left:-120px;background:linear-gradient(100deg,transparent,rgba(255,255,255,.7),transparent);transform:skewX(-20deg);animation:kzs 3.6s 1.5s ease-in-out infinite}
@keyframes kzs{0%{left:-120px}40%,100%{left:120%}}
/* intro et fin : halo rotatif */
.slide.intro .cadre::after,.slide.fin .cadre::after{content:"";position:absolute;right:-180px;top:50%;width:980px;height:980px;margin-top:-490px;border-radius:50%;pointer-events:none;z-index:-1;background:conic-gradient(from var(--ang),transparent 0 55%,rgba(255,122,26,.55) 75%,rgba(18,194,176,.55) 90%,transparent);-webkit-mask:radial-gradient(circle,transparent 58%,#000 59%,#000 61%,transparent 62%);mask:radial-gradient(circle,transparent 58%,#000 59%,#000 61%,transparent 62%);animation:ang 9s linear infinite;filter:drop-shadow(0 0 24px rgba(255,122,26,.6))}
/* barre de commandes */
.cmd{padding:14px max(22px,env(safe-area-inset-right)) max(14px,env(safe-area-inset-bottom)) max(22px,env(safe-area-inset-left))}
.cmd button,.cmd a{border:1px solid rgba(255,255,255,.18);box-shadow:0 8px 24px rgba(0,0,0,.35)}
.cmd button:focus-visible,.cmd a:focus-visible{outline:3px solid var(--o);outline-offset:3px}
body.cache{cursor:none}
/* responsive : la scène s'adapte à toutes les tailles ; en portrait elle pivote */
@media (max-width:820px),(max-height:520px){.cmd button,.cmd a{width:44px;height:44px;font-size:19px}.cmd{padding-top:8px}.cmd .cp{font-size:15px}}
@media (hover:none){.cmd{opacity:1;pointer-events:auto}body.cache{cursor:auto}}
@media (prefers-color-scheme:light){html,body{background:radial-gradient(120% 90% at 50% 40%,#06303a 0,#010a0d 75%)}}
#stage,#stage *{-webkit-font-smoothing:antialiased}
.slide.actif .cadre{will-change:transform,opacity}

/* sélecteur de pages */
#nav{position:fixed;inset:0;z-index:80;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(1,10,13,.72);backdrop-filter:blur(10px);opacity:0;pointer-events:none;transition:opacity .3s}
#nav.ouvert{opacity:1;pointer-events:auto}
.nv{width:min(1100px,100%);max-height:100%;overflow:auto;border-radius:24px;padding:22px;background:linear-gradient(160deg,rgba(14,60,70,.96),rgba(3,20,26,.97));border:1px solid rgba(255,255,255,.18);box-shadow:0 40px 120px rgba(0,0,0,.7);transform:translateY(24px) scale(.97);transition:transform .4s cubic-bezier(.3,1.4,.4,1)}
#nav.ouvert .nv{transform:none}
.nvh{display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.nvh b{font-size:24px;font-weight:800;color:#fff}.nvh span{flex:1;color:#9DB9BD;font-size:15px;font-weight:600}
.nvh button{width:42px;height:42px;border-radius:50%;border:0;background:rgba(255,255,255,.14);color:#fff;font-size:18px;cursor:pointer}
.nvh button:hover{background:#FF7A1A}
.nvg{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.nvg button{display:flex;align-items:center;gap:10px;text-align:left;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.06);color:#e8f4f4;font:700 14px/1.15 'Barlow',system-ui,sans-serif;cursor:pointer;transition:transform .2s,background .2s,border-color .2s}
.nvg button i{flex:none;min-width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-style:normal;font-weight:800;font-size:16px;background:rgba(255,255,255,.14);color:#fff}
.nvg button span{overflow:hidden;overflow-wrap:anywhere;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.nvg button:hover,.nvg button:focus-visible{transform:translateY(-3px);background:rgba(255,122,26,.22);border-color:#FF7A1A;outline:0}
.nvg button.cur{background:#FF7A1A;border-color:#FFB36B;color:#031a21}.nvg button.cur i{background:#031a21;color:#FFB36B}
.nvg button.ch{border-color:rgba(18,194,176,.6)}
#sauter{position:fixed;left:50%;top:18px;transform:translateX(-50%);z-index:90;padding:10px 22px;border-radius:30px;background:#FF7A1A;color:#031a21;font:800 26px 'Barlow',sans-serif;box-shadow:0 10px 30px rgba(0,0,0,.5);display:none}

/* =====================================================================
   MODE VERTICAL (téléphones et tablettes en portrait) : scène 1080 × 1920
   ===================================================================== */
body.vert .logo{left:36px;top:28px;height:64px;padding:5px 14px}
body.vert .tag{display:none}
body.vert .cadre{padding:0 50px}
body.vert .tete{left:50px;right:50px;top:150px;gap:22px}
body.vert .num{min-width:76px;height:76px;font-size:62px;border-radius:20px}
body.vert .tete h2{font-size:84px}
body.vert .kz{font-size:32px;padding:8px 24px;letter-spacing:.22em}
body.vert .intro h1{font-size:138px}
body.vert .intro h1 .l2{font-size:.72em}
body.vert .intro .k{font-size:35px;letter-spacing:.24em}
body.vert .intro .ss{font-size:51px;max-width:900px}
body.vert .fin h1{font-size:230px}
body.vert .fin p{font-size:59px;max-width:900px}
body.vert .pts{left:50px;right:50px;top:340px;gap:34px}
body.vert .pts li{font-size:81px;gap:28px}
body.vert .dense2 .pts{top:310px;gap:18px}body.vert .dense2 .pts li{font-size:46px}
body.vert .dense .pts{top:320px;gap:22px}body.vert .dense .pts li{font-size:51px}
body.vert .pts li i{margin-top:14px;width:20px;height:20px}
/* annonce des membres */
body.vert .bign b{font-size:260px}
body.vert .annt{font-size:68px;max-width:900px}
body.vert .anns{font-size:43px}
body.vert .avs{gap:16px;flex-wrap:wrap}
body.vert .av{width:118px;height:118px}
/* pages d'annonce */
body.vert .ct{font-size:128px;max-width:960px}
body.vert .cs{font-size:49px;max-width:900px}
body.vert .cn{font-size:38px;padding:10px 34px}
body.vert .cl{width:400px}
body.vert .s1{width:2400px;height:2400px}body.vert .s2{width:1500px;height:1500px}
body.vert .spot{width:1000px;margin-left:-500px}
/* fiche profil */
body.vert .pfo{left:190px;top:260px;width:700px;height:820px}
body.vert .pfn{left:50px;right:50px;top:1160px;font-size:113px;text-align:center}
body.vert .prof.lg .pfn{font-size:78px;top:1170px}
body.vert .prs{left:50px;right:50px;top:1340px;gap:14px}
body.vert .prof.lg .prs{top:1360px}
body.vert .pr{padding:12px 28px}body.vert .pr small{font-size:30px}body.vert .pr strong{font-size:51px}
/* badges, diplômes, tickets */
body.vert .titre{left:50px;right:50px;top:150px;font-size:62px;white-space:normal;line-height:1.08}
body.vert .var.port .hero{left:190px;top:330px;width:700px;height:900px}
body.vert .var.port .panneau{left:50px;right:50px;top:1270px;height:560px}
body.vert .var.land .hero{left:50px;top:330px;width:980px;height:690px}
body.vert .var.land .panneau{left:50px;right:50px;top:1070px;height:760px}
body.vert .infos{height:230px}
body.vert .info h3{font-size:94px}body.vert .land .info h3{font-size:81px}
body.vert .info small{font-size:35px}
body.vert .land .vg{flex:0 0 calc(33.333% - 14px)}
body.vert .vg span{font-size:32px}
body.vert .var .grille{left:50px;right:50px;top:310px;bottom:60px;gap:24px;flex-wrap:wrap}
body.vert .grille img{height:380px}
body.vert .land .grille img{width:900px;height:auto}
body.vert .land.n3 .grille img,body.vert .land.n4 .grille figure img{width:470px;height:auto}
body.vert .land.n4 .grille{left:50px;right:50px}
body.vert .tick.land .hero{left:50px;top:330px;width:980px;height:340px}
body.vert .tick.land .panneau{left:50px;right:50px;top:710px;height:520px}
body.vert .tick .infos{position:relative;width:100%;height:170px}
body.vert .tick .info h3{font-size:65px}
body.vert .tick.land .vg{flex:1 1 0;min-width:0}body.vert .tick .vg{min-width:0}body.vert .tick .vg img{width:100%}
body.vert .tick .vign{position:relative;left:auto;right:auto;bottom:auto;width:100%;margin-top:14px;flex-wrap:nowrap;gap:10px}
body.vert .tick.land.n4 .grille,body.vert .tick.land.n5 .grille{left:50px;right:50px;top:310px;bottom:60px;gap:22px}
body.vert .tick.land.n4 .grille figure img,body.vert .tick.land.n5 .grille figure img{width:470px;height:auto}
/* le site : fenêtre + points */
body.vert .site .sh{left:50px;right:50px;top:230px;font-size:92px}
body.vert .site.arch .sh{font-size:81px}
body.vert .mock{left:50px;top:400px;width:980px;height:640px}
body.vert .corps{height:578px}
body.vert .sps{left:50px;right:50px;top:1090px;gap:14px}
body.vert .sp{padding:12px 22px;gap:20px}
body.vert .sp h4{font-size:68px}body.vert .sp p{font-size:49px}
body.vert .n3 .sps{gap:24px}body.vert .n5 .sps{gap:8px}body.vert .n5 .sp{padding:8px 20px}
body.vert .ar{left:50px;right:50px;top:470px;flex-direction:column;justify-content:flex-start}
body.vert .ac{width:100%;padding:36px 44px}
body.vert .ac b{width:64px;height:64px;font-size:51px;margin-bottom:16px}
body.vert .ac h3{font-size:86px}body.vert .ac p{font-size:51px}body.vert .ac li{font-size:59px}
body.vert .ac ul{margin-top:22px;gap:14px}
body.vert .fl{flex:none;width:14px;height:120px;flex-direction:column}
body.vert .fl::before{left:6px;right:auto;top:8px;bottom:8px;width:2px;height:auto}
body.vert .fl u{animation-name:fwv}
@keyframes fwv{0%{transform:translateY(-40px);opacity:0}30%,70%{opacity:1}100%{transform:translateY(40px);opacity:0}}
body.vert .bandeau-s{bottom:200px;font-size:54px;left:50px;right:50px}
body.vert .cmd{padding-bottom:max(14px,env(safe-area-inset-bottom))}
body.vert .site .sh,body.vert .site.arch .sh{font-size:64px;line-height:1.02;top:225px}
body.vert .titre{font-size:56px}
body.vert .zn em{font-size:32px;left:70px;top:10px}body.vert .zn small{width:40px;height:40px;font-size:26px}
body.vert .zn{padding-top:58px}
body.vert .mock .url{font-size:30px;height:48px}body.vert .mock .bar{height:78px}
body.vert .info h3,body.vert .land .info h3{margin-top:16px;font-size:60px}body.vert .tick .info h3{font-size:50px}
body.vert .cmd button,body.vert .cmd a{width:48px;height:48px}

/* =====================================================================
   MODE LÉGER (téléphones et appareils modestes) : mêmes animations clés,
   sans les effets coûteux (flous, ombres portées animées, fondus de couleur)
   ===================================================================== */
body.lite *,body.lite *::before,body.lite *::after{backdrop-filter:none!important;-webkit-backdrop-filter:none!important}
body.lite .orb{filter:none;animation:none;opacity:.35}
body.lite .grid,body.lite .balayage,body.lite #spot,body.lite .cm{display:none}
body.lite #geo i{animation:none}
body.lite #geo .g1,body.lite #geo .g2,body.lite #geo .g3{animation:gt 30s linear infinite}
body.lite #geo .g4,body.lite #geo .g5,body.lite #geo .g6,body.lite #geo .g7,body.lite #geo .g8,body.lite #geo .g9,body.lite #geo .g10{display:none}
body.lite .ac.on,body.lite .slide.actif .mock.a{animation:none}
body.lite .ac.on::after,body.lite .sp.cur::after,body.lite .mock::after,body.lite .kz::after,body.lite .slide.intro .cadre::after,body.lite .slide.fin .cadre::after,body.lite .pfi::after{display:none}
body.lite .ac,body.lite .carte{box-shadow:0 10px 30px rgba(0,0,0,.35)}
body.lite .sp.cur{box-shadow:none;transform:none}
body.lite .sp.cur b,body.lite .zn.cur,body.lite .num,body.lite .carte b{animation:none}
body.lite .sp,body.lite .pr,body.lite .mb{filter:none!important}
body.lite .ct,body.lite .bign b,body.lite .annt,body.lite .stars u,body.lite .stars i{filter:none!important}
body.lite .zn.on span{animation:none}
body.lite .pfi{animation:none}
body.lite .s1,body.lite .s2{animation-duration:2s!important}
body.lite .slide.actif .s1,body.lite .slide.actif .s2{animation:sa 1.6s .4s cubic-bezier(.2,.9,.2,1) both}
body.lite .slide.actif .s3{animation:sa 1.2s .6s cubic-bezier(.2,.9,.2,1) both}
body.lite .stars u:nth-child(n+8){display:none}
body.lite .var .hero img.on{animation:none}
body.lite .slide.actif .av{animation:avp .9s cubic-bezier(.2,1.3,.3,1) both}
body.lite #stage::after{display:none}
body.lite .intro h1 .l2{text-shadow:none}
</style>
</head>
<body>
<div id="stage">
  <div class="bg"></div><div class="grid"></div><div class="orb o1"></div><div class="orb o2"></div><div id="spot"></div><i class="cm"></i><i class="cm c2"></i><i class="cm c3"></i><div id="geo"><i class="g1"></i><i class="g2"></i><i class="g3"></i><i class="g4"></i><i class="g5"></i><i class="g6"></i><i class="g7"></i><i class="g8"></i><i class="g9"></i><i class="g10"></i></div><canvas id="ptc" width="1920" height="1080"></canvas><div class="balayage"></div>
  <div class="logo"><img src="<?= BASE_URL ?>/assets/img/pres_bandeau.png" alt="AEEMCI — Secrétariat Régional Abidjan Nord — Sous-comités Anyama 1 &amp; 2 — JOSPIA"></div>
  <div class="tag">Bataille de Kirina</div>

  <section class="slide intro" data-steps="1"><div class="cadre">
    <div class="k a" style="--d:0">Bataille de Kirina</div>
    <h1><?= mots('COMMISSION', 1) ?><span class="l2"><?= mots('ADMINISTRATION', 3) ?></span></h1>
    <div class="ss a" style="--d:9">Réalisations, travaux non effectués et difficultés</div>
    <div class="ligne"></div>
  </div></section>

  <?php
  afficherListe('', 'CAHIER DES CHARGES : LES MISSIONS', [
    "S'occuper des affaires administratives avant, pendant et après le séminaire",
    "Saisir et imprimer les diplômes, concevoir et tirer les badges et tous autres documents concernant le séminaire",
    "Donner de la qualité à l'administration du séminaire",
    "Participer aux différentes réunions et travaux préliminaires du séminaire, et faire de même pour ses auxiliaires ou collaborateurs",
    "Élaborer et présenter le plan d'action ainsi que le budget de la commission",
    "Porter secours à d'autres commissions pendant ses temps libres, sous l'autorisation des Managers Généraux",
    "Participer activement à l'aménagement du site avant et après le séminaire"], false, 'dense');
  afficherListe('', 'CAHIER DES CHARGES : BON À SAVOIR', [
    "Sollicitations financières : seule la commission Finances est habilitée à solliciter officiellement les personnes physiques ou morales pour le compte du séminaire",
    "Restauration : la commission Restauration assure les repas de tous les participants",
    "Matériel et logistique : chaque commission réunit son matériel, la commission Logistique, Transport et Hébergement l'achemine",
    "Bilan : chaque commission transmet son bilan d'activités dans un délai maximal de dix (10) jours après la clôture du séminaire"], true, 'dense');
  ?>
  <?php
  if ($membresAdmin) { afficherAnnonceMembres($membresAdmin); afficherProfils($membresAdmin); }
  afficherListe('', "PLAN D'ACTION : L'ÉQUIPE", [
    "Cabinet du séminaire : gestion administrative complète, avant, pendant et après l'événement",
    "SERE ALI : Responsable, supervision générale",
    "BARRO Addoul Karim : 1er adjoint, appui à la supervision",
    "SEKONGO : Membre, gestion documentaire",
    "ILIACE : Membre, badges et accréditations",
    "ZONO : Membre, logistique administrative",
    "ZEÏNAB : Membre, secrétariat et archives",
    "OUANDE Amidou : Membre, appui à SEKONGO, ILIACE et ZONO"], true, 'dense2');
  afficherListe('', "PLAN D'ACTION : AVANT LE SÉMINAIRE", [
    "SERE ALI : Convoquer et animer les réunions, valider le plan d'action et le budget, assurer le lien avec les Managers Généraux",
    "BARRO Addoul Karim : Seconder le responsable, vérifier l'avancement des tâches, le remplacer en son absence",
    "SEKONGO : Préparer les modèles de diplômes, collecter la liste officielle des participants, classer et archiver les documents",
    "ILIACE : Concevoir les gabarits de badges par catégorie, imprimer et plastifier les badges, préparer le kit d'accueil",
    "ZONO : Inventorier le matériel (imprimante, cartouches, plastifieuse…), coordonner son acheminement avec la commission Logistique",
    "ZEÏNAB : Rédiger et diffuser les convocations, tenir le registre des décisions, gérer la correspondance",
    "OUANDE Amidou : Seconder SEKONGO, ILIACE et ZONO dans la préparation des documents, des badges et du matériel"], true, 'dense2');
  afficherListe('', "PLAN D'ACTION : PENDANT LE SÉMINAIRE", [
    "SERE ALI : Veiller au bon déroulement des activités administratives, autoriser les soutiens aux autres commissions, remonter les problèmes aux Managers Généraux",
    "BARRO Addoul Karim : Coordonner les membres sur site, gérer les imprévus administratifs, tenir un journal de bord journalier",
    "SEKONGO : Finaliser et imprimer les diplômes, gérer les corrections de dernière minute, archiver chaque document produit",
    "ILIACE : Distribuer les badges à l'arrivée, gérer les badges perdus ou manquants, tenir le registre des présences",
    "ZONO : Superviser le matériel (encre, papier…), appuyer les autres commissions si temps libre, gérer l'espace de travail",
    "ZEÏNAB : Prendre les notes des séances plénières, rédiger les PV à chaud, classer les documents au fil des sessions",
    "OUANDE Amidou : Appuyer SEKONGO, ILIACE et ZONO sur les diplômes, les badges et le matériel"], true, 'dense2');
  afficherListe('', "PLAN D'ACTION : APRÈS LE SÉMINAIRE", [
    "SERE ALI : Valider le bilan d'activités, le transmettre aux Managers Généraux dans les 10 jours, évaluer les performances de l'équipe",
    "BARRO Addoul Karim : Compiler les rapports de chaque membre, rédiger la première version du bilan, assister SERE ALI dans la finalisation",
    "SEKONGO : Distribuer les diplômes aux lauréats, constituer et classer les archives complètes, numériser les documents importants",
    "ILIACE : Dresser le bilan des accréditations, récupérer et inventorier les badges restants, rédiger son rapport individuel",
    "ZONO : Inventorier et restituer le matériel, signaler les pertes ou dommages, rédiger son rapport logistique",
    "ZEÏNAB : Finaliser et classer les PV et comptes-rendus, préparer le dossier d'archivage définitif, rédiger son rapport de secrétariat",
    "OUANDE Amidou : Appuyer SEKONGO, ILIACE et ZONO dans les archives, le bilan des badges et la restitution du matériel"], true, 'dense2');
  afficherListe('', "PLAN D'ACTION : RÈGLES DE FONCTIONNEMENT", [
    "Finances : seule la commission Finances sollicite officiellement des sponsors, l'Administration peut proposer des noms de donateurs",
    "Restauration : la commission Restauration gère les repas, toute dérogation est signalée à l'avance",
    "Matériel : chaque membre réunit son propre matériel, la commission Logistique se charge uniquement de l'acheminement",
    "Bilan d'activités : délai maximum de 10 jours après la clôture pour le transmettre aux Managers Généraux",
    "Soutien inter-commissions : uniquement avec l'autorisation de SERE ALI ou BARRO Addoul Karim, pendant les temps libres"], true, 'dense');
    afficherChapitre('01', 'LES BADGES', 'Séminaristes · Membres de commission · Médias, visiteurs, formateurs', 'rideau');
  afficherVariantes('RÉALISATIONS : LE BADGE DES SÉMINARISTES', 'badges', 'seminariste', $BA);
  afficherVariantes('RÉALISATIONS : LE BADGE DES MEMBRES DE COMMISSION', 'badges', 'commission', $BA);
  afficherVariantes('RÉALISATIONS : LES AUTRES BADGES', 'badges', 'autre', $AB, 'jpg');
  afficherChapitre('02', 'LES DIPLÔMES', 'Membres de commission · Séminaristes · Officiels', 'etoile');
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES MEMBRES DE COMMISSION', 'diplomes', 'commission', $DC, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DU SÉMINARISTE', 'diplomes', 'seminariste', $DS, 'jpg', true);
  afficherVariantes('RÉALISATIONS : LE DIPLÔME DES OFFICIELS', 'diplomes', 'officiel', $OF, 'jpg', true);
  afficherChapitre('03', 'LES TICKETS', 'Cinq modèles de tickets avec souche à conserver', 'rideau');
  afficherVariantes('RÉALISATIONS : LES TICKETS', 'tickets', 'ticket', $TK, 'jpg', true);
  afficherChapitre('04', 'LE SITE JOSPIA', 'Du front-end au back-end, page par page', 'etoile');
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
  <div class="g"><?php if (!defined('PRESENTATION_PUBLIQUE')): ?><a href="<?= BASE_URL ?>/admin/administration" title="Quitter" aria-label="Quitter">&#10005;</a><?php endif; ?><button id="menu" type="button" title="Aller à une page (G)" aria-label="Liste des pages">&#9638;</button><button id="plein" type="button" title="Plein écran" aria-label="Plein écran">&#x26F6;</button><div class="cp" id="cpt"></div></div>
</div>
<div id="nav" aria-hidden="true"><div class="nv"><div class="nvh"><b>Aller à une page</b><span>Cliquez sur un numéro · ou tapez le numéro puis Entrée</span><button type="button" id="nvx" aria-label="Fermer">&#10005;</button></div><div class="nvg" id="nvg"></div></div></div>
<script>
(function(){
  var stage=document.getElementById('stage');
  var LITE=(/[?&]lite=1/.test(location.search))||(!/[?&]full=1/.test(location.search)&&(matchMedia('(hover:none)').matches||innerWidth<900||(navigator.hardwareConcurrency||8)<=4||(navigator.deviceMemory||8)<=4));
  if(LITE)document.body.classList.add('lite');
  /* sécurité téléphone : si la page est zoomée (pincement, zoom d'accessibilité), on remet le zoom à 1 */
  function dezoom(){var m=document.getElementById('vp');if(!m)return;var vv=window.visualViewport;if(vv&&vv.scale>1.02){m.setAttribute('content','width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover');setTimeout(function(){m.setAttribute('content','width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover')},300)}}
  if(window.visualViewport){visualViewport.addEventListener('resize',dezoom)}
  addEventListener('load',dezoom);
  var pivot=false,SW=1920,SH=1080,vert=null;
  function echelle(){var w=innerWidth,h=innerHeight,v=h>w;
    if(v!==vert||(v&&SH!==Math.max(1920,Math.min(2700,Math.round(1080*h/w))))){vert=v;SW=v?1080:1920;SH=v?Math.max(1920,Math.min(2700,Math.round(1080*h/w))):1080;document.body.classList.toggle('vert',v);stage.style.setProperty('--W',SW+'px');stage.style.setProperty('--H',SH+'px');
      ['ptc','fete'].forEach(function(id){var c=document.getElementById(id);if(c){c.width=SW;c.height=SH}});
            }
    stage.style.setProperty('--s',Math.min(w/SW,h/SH));stage.style.setProperty('--r','0deg')}
  echelle();addEventListener('resize',echelle);addEventListener('orientationchange',echelle);

  /* particules lumineuses */
  var pc=document.getElementById('ptc'),px=pc.getContext('2d'),PT=[];
  for(var i=0;i<(LITE?14:60);i++)PT.push({x:Math.random()*1920,y:Math.random()*1080,r:1.5+Math.random()*4,v:.15+Math.random()*.6,a:.15+Math.random()*.45,c:Math.random()<.5?'255,140,50':'80,220,205',p:Math.random()*6});
  var sk=0;(function anim(t){if(LITE&&(sk=!sk)){requestAnimationFrame(anim);return}px.clearRect(0,0,SW,SH);
    PT.forEach(function(p){p.y-=p.v;p.x+=Math.sin(t/2000+p.p)*.35;if(p.y<-10){p.y=SH+10;p.x=Math.random()*SW}if(p.x>SW+20)p.x=Math.random()*SW
      px.beginPath();px.fillStyle='rgba('+p.c+','+p.a+')';if(!LITE){px.shadowColor='rgba('+p.c+',.9)';px.shadowBlur=8}px.arc(p.x,p.y,p.r,0,6.283);px.fill()});
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
        s.classList.toggle('fx',st>=3);
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
      if(sl[i].classList.contains('chap')){var rd=sl[i].classList.contains('rideau');[0,1,2].forEach(function(k){setTimeout(function(){boom(960,540,90);choc(960,540)},(rd?1800:1100)+k*350)})}
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
    var sr=stage.getBoundingClientRect(),k=sr.width/SW||1;
    if(!pivot){var sx=(e.clientX-sr.left)/k,sy=(e.clientY-sr.top)/k;document.getElementById('spot').style.transform='translate('+sx+'px,'+sy+'px)';
      var ang=Math.sqrt(x*x+y*y)*9,tl=(-y)+' '+x+' 0 '+ang+'deg';
      [].forEach.call(document.querySelectorAll('.slide.actif .mock,.slide.actif .ac'),function(m){m.style.setProperty('--tilt',tl)})}
    document.body.classList.add('actifs');document.body.classList.remove('cache');clearTimeout(document._m);document._m=setTimeout(function(){document.body.classList.remove('actifs');document.body.classList.add('cache')},3000);
  });

  /* sélecteur de pages : un bouton par page */
  (function(){
    var nav=document.getElementById('nav'),g=document.getElementById('nvg'),saisie='',tm=null;
    sl.forEach(function(el,i){
      var t=el.querySelector('h2,h1'),txt=(t?t.textContent:'').replace(/\s+/g,' ').trim();
      if(!txt)txt=el.classList.contains('fin')?'Merci':'Page '+(i+1);
      if(el.classList.contains('intro'))txt='Titre';
      txt=txt.replace(/^RÉALISATIONS\s*:\s*/i,'').replace(/^LA COMMISSION /i,'Commission ').replace(/^L'INSCRIPTION DES /i,'Inscription ').replace(/^MEMBRES DANS LA COMMISSION ADMINISTRATION/i,'Les membres');
      var b=document.createElement('button');b.type='button';b.title=txt;
      if(el.classList.contains('chap'))b.className='ch';
      b.innerHTML='<i>'+(i+1)+'</i><span></span>';b.lastChild.textContent=txt.length>44?txt.slice(0,43)+'…':txt;
      b.onclick=function(){fermer();go(i,0,true)};g.appendChild(b);
    });
    function marquer(){[].forEach.call(g.children,function(b,i){b.classList.toggle('cur',i===c)})}
    function ouvrir(){marquer();nav.classList.add('ouvert');nav.setAttribute('aria-hidden','false');var cb=g.children[c];if(cb)cb.focus({preventScroll:false})}
    function fermer(){nav.classList.remove('ouvert');nav.setAttribute('aria-hidden','true')}
    document.getElementById('menu').onclick=function(){nav.classList.contains('ouvert')?fermer():ouvrir()};
    document.getElementById('nvx').onclick=fermer;
    nav.addEventListener('click',function(e){if(e.target===nav)fermer()});
    var bulle=document.createElement('div');bulle.id='sauter';document.body.appendChild(bulle);
    document.addEventListener('keydown',function(e){
      var ouvert=nav.classList.contains('ouvert');
      if(e.key==='g'||e.key==='G'){e.preventDefault();ouvert?fermer():ouvrir();return}
      if(e.key==='Escape'&&ouvert){fermer();return}
      if(/^[0-9]$/.test(e.key)&&!ouvert){saisie+=e.key;bulle.textContent='Page '+saisie;bulle.style.display='block';clearTimeout(tm);tm=setTimeout(function(){saisie='';bulle.style.display='none'},2500);return}
      if(e.key==='Enter'&&saisie&&!ouvert){var k=parseInt(saisie,10);saisie='';bulle.style.display='none';if(k>=1&&k<=n)go(k-1,0,true);e.stopImmediatePropagation();e.preventDefault()}
    },true);
    /* les flèches du clavier ne naviguent pas quand le sélecteur est ouvert */
    document.addEventListener('keydown',function(e){if(nav.classList.contains('ouvert')&&['ArrowRight','ArrowLeft','PageDown','PageUp',' ','Enter','Backspace','Home','End'].indexOf(e.key)>=0){e.stopImmediatePropagation()}},true);
  })();
  /* feu d'artifice de fin */
  var fc=document.getElementById('fete'),fx=fc.getContext('2d'),F=[],run=false,col=['255,122,26','18,194,176','255,255,255','255,179,107'];
  var stg=document.getElementById('stage');
  function boom(x,y,n,abs){if(!abs){x=x*SW/1920;y=y*SH/1080}if(LITE)n=Math.round(n*.4);for(var k=0;k<n;k++){var a=Math.random()*6.283,v=3+Math.random()*10;F.push({x:x,y:y,vx:Math.cos(a)*v,vy:Math.sin(a)*v-2,l:1,c:col[k%4]})}if(!run){run=true;bo()}}
  function choc(x,y,abs){if(!abs){x=x*SW/1920;y=y*SH/1080}(LITE?[0]:[0,1]).forEach(function(t){var d=document.createElement('div');d.className='shock'+(t?' t':'');d.style.left=x+'px';d.style.top=y+'px';stg.appendChild(d);setTimeout(function(){d.remove()},1400)})}
  function exploser(el,gauche){
    if(!el)return;var r=el.getBoundingClientRect(),sr=stg.getBoundingClientRect(),k=sr.width/SW||1;
    var x=gauche?(r.left-sr.left)/k+50:(r.left-sr.left+r.width/2)/k,y=(r.top-sr.top+r.height/2)/k;
    boom(x,y,55,true);choc(x,y,true);
  }
  function fete(){
    for(var b=0;b<9;b++)(function(b){setTimeout(function(){var cx=(300+Math.random()*1320)*SW/1920,cy=(200+Math.random()*420)*SH/1080;
      for(var k=0;k<(LITE?30:70);k++){var a=Math.random()*6.283,v=3+Math.random()*7;F.push({x:cx,y:cy,vx:Math.cos(a)*v,vy:Math.sin(a)*v,l:1,c:col[k%4]})}
      if(!run){run=true;bo()}},b*420)})(b);
  }
  function bo(){fx.clearRect(0,0,SW,SH);
    F=F.filter(function(p){return p.l>0});
    F.forEach(function(p){p.x+=p.vx;p.y+=p.vy;p.vy+=.12;p.vx*=.985;p.l-=.011;fx.beginPath();fx.fillStyle='rgba('+p.c+','+Math.max(p.l,0)+')';if(!LITE){fx.shadowColor='rgba('+p.c+',1)';fx.shadowBlur=8}fx.arc(p.x,p.y,3,0,6.283);fx.fill()});
    if(F.length)requestAnimationFrame(bo);else run=false}
  var h=parseInt((location.hash||'').slice(1),10);
  go(h>=1&&h<=n?h-1:0,0,true);
})();
</script>
</body>
</html>
