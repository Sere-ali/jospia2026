<?php
/**
 * Génération PDF (A4) des badges et diplômes JOSPIA 2026, côté serveur (GD + écriture PDF directe).
 *  - badges : 4 par page A4 (portrait, 2 x 2) ;
 *  - diplômes : 1 par page A4 (paysage).
 * Même mise en page que l'aperçu HTML (mêmes maquettes, mêmes coordonnées).
 */

const PDF_FONTS = __DIR__ . '/../assets/fonts/ttf/';
// Métriques verticales (ascendante, descendante) en fraction du corps, pour centrer comme le navigateur
const PDF_METRIQUES = [
    'barlow-latin-800-normal.ttf'              => [1.0, 0.2],
    'barlow-latin-700-normal.ttf'              => [1.0, 0.2],
    'barlow-semi-condensed-latin-700-normal.ttf' => [1.0, 0.2],
    'anton-latin-400-normal.ttf'               => [1.1763, 0.3291],
    'tinos-latin-400-normal.ttf'               => [0.8911, 0.2163],
    'tinos-latin-700-normal.ttf'               => [0.8911, 0.2163],
];

function pdfPolice($nom) { return PDF_FONTS . $nom; }

/** Largeur (px) d'un texte avec espacement de lettres $ls (px). */
function pdfLargeurTexte($taille, $police, $texte, $ls = 0.0) {
    if ($texte === '') return 0;
    $b = imagettfbbox($taille, 0, pdfPolice($police), $texte);
    $l = max($b[2], $b[4]) - min($b[0], $b[6]);
    return $l + $ls * max(0, mb_strlen($texte, 'UTF-8') - 1);
}

/** Écrit un texte à partir de x, ligne de base y (avec ombre et espacement de lettres facultatifs). */
function pdfEcrire($img, $taille, $x, $y, $couleur, $police, $texte, $ls = 0.0) {
    $f = pdfPolice($police);
    if ($ls == 0.0) { imagettftext($img, $taille, 0, (int)round($x), (int)round($y), $couleur, $f, $texte); return; }
    $chars = preg_split('//u', $texte, -1, PREG_SPLIT_NO_EMPTY);
    $prefixe = '';
    foreach ($chars as $i => $c) {
        $dx = $i === 0 ? 0 : (pdfLargeurTexte($taille, $police, $prefixe . '|') - pdfLargeurTexte($taille, $police, '|')) + $ls * $i;
        imagettftext($img, $taille, 0, (int)round($x + $dx), (int)round($y), $couleur, $f, $c);
        $prefixe .= $c;
    }
}

function pdfBaseline($police, $taille, $centreY) {
    [$a, $d] = PDF_METRIQUES[$police];
    return $centreY + ($a - $d) / 2 * $taille;
}

/** Texte centré (ou aligné à gauche) dans une boîte, ajusté en largeur (équivaut à data-fit). */
function pdfTexteBoite($img, $S, $police, $tailleBase, $fit, $texte, $bx, $by, $bw, $bh, $couleur, $centre = true, $ls = 0.0, $ombre = null) {
    $taille = $tailleBase * $S;
    $l = pdfLargeurTexte($taille, $police, $texte, $ls * $S);
    $dispo = $bw * $S * $fit;
    if ($l > $dispo && $l > 0) { $taille *= $dispo / $l; $l = $dispo; }
    $x = $bx * $S + ($centre ? ($bw * $S - pdfLargeurTexte($taille, $police, $texte, $ls * $S)) / 2 : 0);
    $y = pdfBaseline($police, $taille, ($by + $bh / 2) * $S);
    if ($ombre) { pdfEcrire($img, $taille, $x, $y + 3 * $S, $ombre, $police, $texte, $ls * $S); }
    pdfEcrire($img, $taille, $x, $y, $couleur, $police, $texte, $ls * $S);
}

function pdfChargerImage($chemin) {
    $ext = strtolower(pathinfo($chemin, PATHINFO_EXTENSION));
    if ($ext === 'webp') return imagecreatefromwebp($chemin);
    if ($ext === 'png') return imagecreatefrompng($chemin);
    return imagecreatefromjpeg($chemin);
}

/** Photo du dossier uploads (ou de la base si le disque a été réinitialisé) sous forme de ressource GD, ou null. */
function pdfPhoto(PDO $pdo, $nom) {
    if (!$nom || !preg_match('/^[A-Za-z0-9._-]{1,120}$/', $nom)) return null;
    $donnees = null;
    $fichier = __DIR__ . '/../uploads/photos/' . $nom;
    if (is_file($fichier)) { $donnees = @file_get_contents($fichier); }
    if ($donnees === null || $donnees === false || $donnees === '') {
        try {
            photoTableBase($pdo);
            $st = $pdo->prepare("SELECT donnees FROM photos_stockees WHERE nom = ?");
            $st->execute([$nom]);
            $donnees = $st->fetchColumn();
        } catch (Throwable $e) { $donnees = null; }
    }
    if (!$donnees) return null;
    $im = @imagecreatefromstring($donnees);
    return $im ?: null;
}

/** Colle $src dans le rectangle (x,y,w,h) façon object-fit: cover avec object-position (px%, py%). */
function pdfCouvrir($dst, $src, $x, $y, $w, $h, $px, $py) {
    $sw = imagesx($src); $sh = imagesy($src);
    $r = max($w / $sw, $h / $sh);
    $cw = $w / $r; $ch = $h / $r;
    $sx = ($sw - $cw) * $px; $sy = ($sh - $ch) * $py;
    imagecopyresampled($dst, $src, (int)round($x), (int)round($y), (int)round($sx), (int)round($sy), (int)round($w), (int)round($h), (int)round($cw), (int)round($ch));
}

function pdfToile($w, $h) {
    $im = imagecreatetruecolor($w, $h);
    imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
    imagealphablending($im, true);
    return $im;
}

function pdfModele($toile, $fichier, $W, $H) {
    $m = pdfChargerImage(__DIR__ . '/../assets/img/modeles/' . $fichier);
    imagealphablending($toile, true);
    imagecopyresampled($toile, $m, 0, 0, 0, 0, $W, $H, imagesx($m), imagesy($m));
    imagedestroy($m);
}

function pdfPlaceholderPhoto($toile, $x, $y, $w, $h) {
    imagefilledrectangle($toile, (int)$x, (int)$y, (int)($x + $w), (int)($y + $h), imagecolorallocate($toile, 221, 226, 223));
}

/** Badge Séminariste (orange) 904 x 1280 -> image GD. */
function pdfBadgeSeminariste(PDO $pdo, array $s, $S = 1.25) {
    $W = (int)round(904 * $S); $H = (int)round(1280 * $S);
    $im = pdfToile($W, $H);
    $ph = pdfPhoto($pdo, $s['photo'] ?? '');
    if ($ph) { pdfCouvrir($im, $ph, 238.5 * $S, 470 * $S, 424 * $S, 424 * $S, 0.5, 0.2); imagedestroy($ph); }
    else { pdfPlaceholderPhoto($im, 238.5 * $S, 470 * $S, 424 * $S, 424 * $S); }
    pdfModele($im, 'badge_seminariste.webp', $W, $H);
    $blanc = imagecolorallocate($im, 255, 255, 255);
    $ombre = imagecolorallocatealpha($im, 0, 0, 0, 100);
    pdfTexteBoite($im, $S, 'barlow-latin-800-normal.ttf', 62, 0.98, mb_strtoupper((string)$s['nom_prenoms'], 'UTF-8'), 112, 902, 680, 66, $blanc, true, 0.62, $ombre);
    $vals = [(string)$s['dortoir'], (string)($s['niveau_affecte'] ?: 'Non affecté'), (string)$s['matricule']];
    foreach ($vals as $i => $v) {
        pdfTexteBoite($im, $S, 'barlow-semi-condensed-latin-700-normal.ttf', 37, 0.97, $v, 403, [994, 1037, 1081][$i], 340, 42, $blanc, false);
    }
    return $im;
}


/** Texte doré (dégradé) sur 1 ou plusieurs lignes centrées en ($cx, $cy). */
function pdfTexteDore($im, $W, $H, array $lignes, $police, $taille, $cx, $cy) {
    $inter = 1.02 * $taille; $n = count($lignes);
    $maxL = 0;
    foreach ($lignes as $l) { $maxL = max($maxL, pdfLargeurTexte($taille, $police, $l, 0.01 * $taille)); }
    $tw = (int)ceil($maxL) + 12; $th = (int)ceil($n * $inter + $taille * 0.5);
    $x0 = (int)floor($cx - $tw / 2); $y0 = (int)floor($cy - $n * $inter / 2 - $taille * 0.15);
    if ($tw <= 0 || $th <= 0) return;
    $masque = imagecreatetruecolor($tw, $th);
    imagefill($masque, 0, 0, imagecolorallocate($masque, 0, 0, 0));
    $bl = imagecolorallocate($masque, 255, 255, 255);
    foreach ($lignes as $i => $l) {
        $lw = pdfLargeurTexte($taille, $police, $l, 0.01 * $taille);
        $centreLigne = $cy - $n * $inter / 2 + ($i + 0.5) * $inter;
        pdfEcrire($masque, $taille, ($tw - $lw) / 2, pdfBaseline($police, $taille, $centreLigne) - $y0, $bl, $police, $l, 0.01 * $taille);
    }
    for ($j = 0; $j < $th; $j++) {
        $t = $j / max(1, $th - 1);
        if ($t < 0.55) { $k = $t / 0.55; $r = 0xF0 + (0xD6 - 0xF0) * $k; $g = 0xCF + (0xA9 - 0xCF) * $k; $b = 0x73 + (0x3F - 0x73) * $k; }
        else { $k = ($t - 0.55) / 0.45; $r = 0xD6 + (0xB9 - 0xD6) * $k; $g = 0xA9 + (0x85 - 0xA9) * $k; $b = 0x3F + (0x26 - 0x3F) * $k; }
        for ($i2 = 0; $i2 < $tw; $i2++) {
            $a = imagecolorat($masque, $i2, $j) & 0xFF;
            if ($a < 4) continue;
            $px = $x0 + $i2; $py = $y0 + $j;
            if ($px < 0 || $py < 0 || $px >= $W || $py >= $H) continue;
            $dst = imagecolorat($im, $px, $py);
            $dr = ($dst >> 16) & 255; $dg = ($dst >> 8) & 255; $db = $dst & 255; $al = $a / 255;
            imagesetpixel($im, $px, $py, ((int)round($dr + ($r - $dr) * $al) << 16) | ((int)round($dg + ($g - $dg) * $al) << 8) | (int)round($db + ($b - $db) * $al));
        }
    }
    imagedestroy($masque);
}

/** Badge Commission (vert) 904 x 1280 -> image GD. */
function pdfBadgeCommission(PDO $pdo, array $m, $S = 1.25) {
    $W = (int)round(904 * $S); $H = (int)round(1280 * $S);
    $im = pdfToile($W, $H);
    $ph = pdfPhoto($pdo, $m['photo'] ?? '');
    if ($ph) { pdfCouvrir($im, $ph, 496 * $S, 10 * $S, 400 * $S, 660 * $S, 0.5, 0.18); imagedestroy($ph); }
    else { pdfPlaceholderPhoto($im, 496 * $S, 10 * $S, 400 * $S, 660 * $S); }
    pdfModele($im, 'badge_commission.webp', $W, $H);
    $blanc = imagecolorallocate($im, 255, 255, 255);
    $ombre = imagecolorallocatealpha($im, 0, 0, 0, 100);

    // Nom sur 1 ou 2 lignes (interligne 1,12), taille commune ajustée à la ligne la plus large
    $lignes = josLignesNom($m['nom_prenoms']);
    $police = 'barlow-latin-800-normal.ttf';
    $taille = 76 * $S; $maxL = 0;
    foreach ($lignes as $l) { $maxL = max($maxL, pdfLargeurTexte($taille, $police, $l, 0.76 * $S)); }
    $dispo = 610 * $S * 0.96;
    if ($maxL > $dispo) { $taille *= $dispo / $maxL; }
    $n = count($lignes);
    $cy = (708 + 172 / 2) * $S;
    foreach ($lignes as $i => $l) {
        $centreLigne = $cy - $n * 0.56 * $taille + ($i + 0.5) * 1.12 * $taille;
        $y = pdfBaseline($police, $taille, $centreLigne);
        $x = 150 * $S + (610 * $S - pdfLargeurTexte($taille, $police, $l, 0.01 * $taille)) / 2;
        pdfEcrire($im, $taille, $x, $y + 3 * $S, $ombre, $police, $l, 0.01 * $taille);
        pdfEcrire($im, $taille, $x, $y, $blanc, $police, $l, 0.01 * $taille);
    }

    // Commission : texte doré entre deux filets (nom complet ; sur 2 lignes si long)
    $com = mb_strtoupper(nomCommissionComplet($m['commission']), 'UTF-8');
    if (mb_strlen($com, 'UTF-8') > 14) {
        $lignesCom = josLignesNom($com);
        $police = 'barlow-latin-800-normal.ttf';
        $taille = 42 * $S; $maxL = 0;
        foreach ($lignesCom as $l) { $maxL = max($maxL, pdfLargeurTexte($taille, $police, $l, 0.01 * $taille)); }
        $dispo = 594 * $S * 0.96;
        if ($maxL > $dispo) { $taille *= $dispo / $maxL; }
        pdfTexteDore($im, $W, $H, $lignesCom, $police, $taille, (160 + 594 / 2) * $S, (980 + 100 / 2) * $S + 2 * $S);
        return $im;
    }
    $police = 'barlow-latin-800-normal.ttf';
    $bx = 232; $bw = 450; $by = 985; $bh = 90;
    $taille = 84 * $S;
    $l = pdfLargeurTexte($taille, $police, $com, 0.01 * $taille);
    $large = false;
    if ($l * 1 > 0 && ($bw * $S * 0.62) / $l < 0.6) { $large = true; $bx = 160; $bw = 594; }
    $f = $large ? ($bw * $S * 0.98) / $l : ($bw * $S * 0.62) / $l;
    if ($f < 1) { $taille *= $f; $l = pdfLargeurTexte($taille, $police, $com, 0.01 * $taille); }
    $x = $bx * $S + ($bw * $S - $l) / 2;
    $yBase = pdfBaseline($police, $taille, ($by + $bh / 2) * $S + 2 * $S);
    // calque de texte pour remplissage dégradé doré
    $x0 = (int)floor($x) - 4; $y0 = (int)floor($yBase - $taille * 1.0); $tw = (int)ceil($l) + 10; $th = (int)ceil($taille * 1.3);
    if ($tw > 0 && $th > 0) {
        $masque = imagecreatetruecolor($tw, $th);
        imagefill($masque, 0, 0, imagecolorallocate($masque, 0, 0, 0));
        $bl = imagecolorallocate($masque, 255, 255, 255);
        pdfEcrire($masque, $taille, $x - $x0, $yBase - $y0, $bl, $police, $com, 0.01 * $taille);
        // ombre portée légère
        for ($j = 0; $j < $th; $j++) {
            $t = $j / max(1, $th - 1);
            // dégradé #F0CF73 -> #D6A93F -> #B98526
            if ($t < 0.55) { $k = $t / 0.55; $r = 0xF0 + (0xD6 - 0xF0) * $k; $g = 0xCF + (0xA9 - 0xCF) * $k; $b = 0x73 + (0x3F - 0x73) * $k; }
            else { $k = ($t - 0.55) / 0.45; $r = 0xD6 + (0xB9 - 0xD6) * $k; $g = 0xA9 + (0x85 - 0xA9) * $k; $b = 0x3F + (0x26 - 0x3F) * $k; }
            for ($i2 = 0; $i2 < $tw; $i2++) {
                $a = imagecolorat($masque, $i2, $j) & 0xFF;
                if ($a < 4) continue;
                $px = $x0 + $i2; $py = $y0 + $j;
                if ($px < 0 || $py < 0 || $px >= $W || $py >= $H) continue;
                $dst = imagecolorat($im, $px, $py);
                $dr = ($dst >> 16) & 255; $dg = ($dst >> 8) & 255; $db = $dst & 255;
                $al = $a / 255;
                imagesetpixel($im, $px, $py, ((int)round($dr + ($r - $dr) * $al) << 16) | ((int)round($dg + ($g - $dg) * $al) << 8) | (int)round($db + ($b - $db) * $al));
            }
        }
        imagedestroy($masque);
    }
    // filets décoratifs (absents en mode "large")
    if (!$large) {
        $or = [0xD6, 0xAA, 0x45];
        $cyL = ($by + $bh / 2) * $S;
        $gauche = [$bx * $S, $x - 10 * $S - 4 * $S];
        $droite = [$x + $l + 10 * $S + 4 * $S, ($bx + $bw) * $S];
        foreach ([[$gauche, false], [$droite, true]] as [$seg, $inverse]) {
            $len = $seg[1] - $seg[0];
            if ($len < 12 * $S) continue;
            for ($i3 = 0; $i3 < $len - 14 * $S; $i3++) {
                $t = $i3 / max(1, $len - 14 * $S);
                $op = $inverse ? (1 - $t) : $t; // plein côté texte, transparent côté bord
                $col = imagecolorallocatealpha($im, $or[0], $or[1], $or[2], (int)round(127 * (1 - $op)));
                $px = $inverse ? $seg[0] + 14 * $S + $i3 : $seg[0] + $i3;
                imagefilledrectangle($im, (int)$px, (int)($cyL - 1.5 * $S), (int)$px, (int)($cyL + 1.5 * $S), $col);
            }
            $dx = $inverse ? $seg[0] + 5.5 * $S : $seg[1] - 5.5 * $S;
            $dxp = $inverse ? $seg[0] + 5.5 * $S : $seg[1] - 5.5 * $S;
            $r = 6.5 * $S; $c = imagecolorallocate($im, $or[0], $or[1], $or[2]);
            imagefilledpolygon($im, [(int)$dxp, (int)($cyL - $r), (int)($dxp + $r), (int)$cyL, (int)$dxp, (int)($cyL + $r), (int)($dxp - $r), (int)$cyL], 4, $c);
        }
    }
    return $im;
}

/** Diplôme séminariste (doré) 1123 x 793. */
function pdfDiplomeSeminariste($nom, $S = 2.0) {
    $W = (int)round(1123 * $S); $H = (int)round(793 * $S);
    $im = pdfToile($W, $H);
    pdfModele($im, 'certificat_seminariste.jpg', $W, $H);
    $noir = imagecolorallocate($im, 0, 0, 0);
    pdfTexteBoite($im, $S, 'anton-latin-400-normal.ttf', 52, 0.98, mb_strtoupper($nom, 'UTF-8'), 190, 343, 750, 62, $noir, true, 0.52);
    return $im;
}

/** Diplôme commission (vert) 1280 x 904. */
function pdfDiplomeCommission($nom, $qualite, $S = 1.75) {
    $W = (int)round(1280 * $S); $H = (int)round(904 * $S);
    $im = pdfToile($W, $H);
    pdfModele($im, 'certificat.jpg', $W, $H);
    $noir = imagecolorallocate($im, 17, 17, 17);
    pdfTexteBoite($im, $S, 'barlow-latin-700-normal.ttf', 56, 0.98, mb_strtoupper($nom, 'UTF-8'), 214, 378, 910, 54, $noir, true, 56 * 0.03);

    // « (JOSPIA) en qualité de XXX , tenue » avec segments en gras
    $segments = [['(', 0], ['JOSPIA', 1], [')', 0], [' en qualité de ', 0], [mb_strtoupper($qualite, 'UTF-8'), 1], [' , tenue', 0]];
    $taille = 29.2 * $S;
    $mesure = function ($t) use ($segments) {
        $w = 0;
        foreach ($segments as [$txt, $gras]) { $w += pdfLargeurTexte($t, $gras ? 'tinos-latin-700-normal.ttf' : 'tinos-latin-400-normal.ttf', $txt . '|') - pdfLargeurTexte($t, $gras ? 'tinos-latin-700-normal.ttf' : 'tinos-latin-400-normal.ttf', '|'); }
        return $w;
    };
    $l = $mesure($taille);
    $dispo = 1000 * $S * 0.96;
    if ($l > $dispo) { $taille *= $dispo / $l; $l = $mesure($taille); }
    $x = 140 * $S + (1000 * $S - $l) / 2;
    $y = pdfBaseline('tinos-latin-400-normal.ttf', $taille, (493 + 17) * $S);
    foreach ($segments as [$txt, $gras]) {
        $p = $gras ? 'tinos-latin-700-normal.ttf' : 'tinos-latin-400-normal.ttf';
        pdfEcrire($im, $taille, $x, $y, $noir, $p, $txt);
        $x += pdfLargeurTexte($taille, $p, $txt . '|') - pdfLargeurTexte($taille, $p, '|');
    }
    return $im;
}

/* ------------------------------------------------------------------ */
/*  Écriture PDF en flux (mémoire constante, même pour des centaines de badges)  */
/* ------------------------------------------------------------------ */
class PdfFlux {
    private $pos = 0;
    private $offsets = [];
    private $pages = [];
    private $prochain = 3; // 1 = catalogue, 2 = arbre des pages
    private $sortie;

    public function __construct($nomFichier) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $nomFichier . '"');
        header('Cache-Control: private, no-store');
        $this->ecrire("%PDF-1.4\n%\xE2\xE3\xCF\xD3\n");
    }
    private function ecrire($s) { echo $s; $this->pos += strlen($s); }
    private function objet($id, $corps) { $this->offsets[$id] = $this->pos; $this->ecrire($id . " 0 obj\n" . $corps . "\nendobj\n"); }

    /** $images : liste de [jpegBinaire, largeurPx, hauteurPx, x, y, w, h] (en points, origine en haut à gauche). */
    public function page($largeurPt, $hauteurPt, array $images, array $traits = []) {
        $xobj = ''; $contenu = '';
        foreach ($images as $k => $im) {
            [$jpeg, $wpx, $hpx, $x, $y, $w, $h] = $im;
            $idImg = $this->prochain++;
            $this->offsets[$idImg] = $this->pos;
            $this->ecrire($idImg . " 0 obj\n<< /Type /XObject /Subtype /Image /Width $wpx /Height $hpx /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length " . strlen($jpeg) . " >>\nstream\n");
            $this->ecrire($jpeg);
            $this->ecrire("\nendstream\nendobj\n");
            $xobj .= "/Im$k $idImg 0 R ";
            $yb = $hauteurPt - $y - $h;
            $contenu .= sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /Im%d Do Q\n", $w, $h, $x, $yb, $k);
        }
        if ($traits) {
            $contenu .= "q 0.75 G 0.4 w [4 3] 0 d\n";
            foreach ($traits as [$x1, $y1, $x2, $y2]) { $contenu .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, $hauteurPt - $y1, $x2, $hauteurPt - $y2); }
            $contenu .= "Q\n";
        }
        $idC = $this->prochain++;
        $this->objet($idC, "<< /Length " . strlen($contenu) . " >>\nstream\n" . $contenu . "endstream");
        $idP = $this->prochain++;
        $this->objet($idP, sprintf("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /XObject << %s>> >> /Contents %d 0 R >>", $largeurPt, $hauteurPt, $xobj, $idC));
        $this->pages[] = $idP;
        flush();
    }

    public function fin() {
        $kids = implode(' ', array_map(function ($i) { return $i . ' 0 R'; }, $this->pages));
        $this->objet(2, "<< /Type /Pages /Kids [ $kids ] /Count " . count($this->pages) . " >>");
        $this->objet(1, "<< /Type /Catalog /Pages 2 0 R >>");
        $xref = $this->pos;
        $n = $this->prochain;
        $this->ecrire("xref\n0 $n\n0000000000 65535 f \n");
        for ($i = 1; $i < $n; $i++) { $this->ecrire(sprintf("%010d 00000 n \n", $this->offsets[$i])); }
        $this->ecrire("trailer\n<< /Size $n /Root 1 0 R >>\nstartxref\n$xref\n%%EOF\n");
    }
}

function pdfJpeg($im, $qualite = 88) {
    ob_start();
    imageinterlace($im, false);
    imagejpeg($im, null, $qualite);
    return ob_get_clean();
}

/**
 * Badges : 4 par page A4 (2 x 2, cases A6 de 105 x 148,5 mm), repères de découpe en pointillés.
 * $generateur : fonction(array $ligne) => ressource GD ; $lignes : liste de lignes SQL.
 */
function pdfBadgesA4(array $lignes, callable $generateur, $nomFichier) {
    @set_time_limit(600);
    @ini_set('memory_limit', '512M');
    $pdf = new PdfFlux($nomFichier);
    $A4w = 595.28; $A4h = 841.89;
    $cw = $A4w / 2; $ch = $A4h / 2;
    $marge = 12; // points
    foreach (array_chunk($lignes, 4) as $groupe) {
        $images = [];
        foreach ($groupe as $i => $ligne) {
            $im = $generateur($ligne);
            $wpx = imagesx($im); $hpx = imagesy($im);
            $jpeg = pdfJpeg($im);
            imagedestroy($im);
            $col = $i % 2; $rang = intdiv($i, 2);
            // badge 904:1280 centré dans sa case
            $h = $ch - 2 * $marge; $w = $h * 904 / 1280;
            if ($w > $cw - 2 * $marge) { $w = $cw - 2 * $marge; $h = $w * 1280 / 904; }
            $x = $col * $cw + ($cw - $w) / 2;
            $y = $rang * $ch + ($ch - $h) / 2;
            $images[] = [$jpeg, $wpx, $hpx, $x, $y, $w, $h];
        }
        $pdf->page($A4w, $A4h, $images, [[$cw, 0, $cw, $A4h], [0, $ch, $A4w, $ch]]);
    }
    if (!$lignes) { $pdf->page($A4w, $A4h, []); }
    $pdf->fin();
    exit;
}

/** Diplôme(s) : une page A4 paysage par diplôme, image plein cadre. */
function pdfDiplomesA4(array $lignes, callable $generateur, $nomFichier) {
    @set_time_limit(600);
    @ini_set('memory_limit', '512M');
    $pdf = new PdfFlux($nomFichier);
    $w = 841.89; $h = 595.28;
    foreach ($lignes as $ligne) {
        $im = $generateur($ligne);
        $wpx = imagesx($im); $hpx = imagesy($im);
        $jpeg = pdfJpeg($im, 90);
        imagedestroy($im);
        $pdf->page($w, $h, [[$jpeg, $wpx, $hpx, 0, 0, $w, $h]]);
    }
    if (!$lignes) { $pdf->page($w, $h, []); }
    $pdf->fin();
    exit;
}

/* ------------------------------------------------------------------ */
/*  Bulletin de notes : 2 par page A4 (cases A5 paysage, 1240 x 877 px)  */
/* ------------------------------------------------------------------ */
function pdfBulletin(PDO $pdo, array $s) {
    $W = 877; $H = 1240;
    $im = pdfToile($W, $H);
    imageantialias($im, true);
    $orange = imagecolorallocate($im, 247, 127, 0); $vert = imagecolorallocate($im, 11, 138, 78);
    $vertF = imagecolorallocate($im, 6, 83, 47); $noir = imagecolorallocate($im, 30, 38, 33);
    $gris = imagecolorallocate($im, 100, 112, 105); $blanc = imagecolorallocate($im, 255, 255, 255);
    $teinte = imagecolorallocate($im, 235, 247, 240); $ligne = imagecolorallocate($im, 214, 226, 219);
    $rouge = imagecolorallocate($im, 192, 57, 43); $or = imagecolorallocate($im, 214, 140, 20);

    // cadre + bandes tricolores
    imagesetthickness($im, 3); imagerectangle($im, 14, 14, $W - 15, $H - 15, $vert); imagesetthickness($im, 1);
    imagefilledrectangle($im, 14, 14, 14 + 3, 14 + 3, $vert);
    imagefilledrectangle($im, 17, 17, (int)($W / 3), 23, $orange);
    imagefilledrectangle($im, (int)($W * 2 / 3), 17, $W - 18, 23, $vert);

    // en-tête du badge (logos AEEMCI + JOSPIA)
    $ent = imagecreatefrompng(__DIR__ . '/../assets/img/bulletin_entete.png');
    $ew = 640; $eh = (int)round($ew * imagesy($ent) / imagesx($ent));
    imagecopyresampled($im, $ent, (int)(($W - $ew) / 2), 34, 0, 0, $ew, $eh, imagesx($ent), imagesy($ent));
    imagedestroy($ent);

    $y = 34 + $eh + 12;
    pdfTexteBoite($im, 1, 'barlow-latin-800-normal.ttf', 42, 0.9, 'BULLETIN DE NOTES', 0, $y, $W, 52, $vertF, true, 3);
    $y += 54;
    imagefilledrectangle($im, (int)($W / 2 - 150), $y, (int)($W / 2 - 1), $y + 4, $orange);
    imagefilledrectangle($im, (int)($W / 2), $y, (int)($W / 2 + 150), $y + 4, $vert);
    $y += 20;

    // identité
    $px = 46; $ph = 190; $pw = 150;
    $photo = pdfPhoto($pdo, $s['photo'] ?? '');
    if ($photo) { pdfCouvrir($im, $photo, $px, $y, $pw, $ph, 0.5, 0.25); imagedestroy($photo); } else { pdfPlaceholderPhoto($im, $px, $y, $pw, $ph); }
    imagesetthickness($im, 3); imagerectangle($im, $px, $y, $px + $pw, $y + $ph, $orange); imagesetthickness($im, 1);

    $test = ($s['dortoir'] ?? '') === 'Pépinière' ? 'Non applicable (Pépinière)'
        : (!empty($s['test_complete']) ? (str_replace('.', ',', (string)$s['note_test']) . ' / 20 - Niveau ' . $s['niveau_affecte']) : 'Non composé');
    $champs = [
        ['Nom et prénoms', $s['nom_prenoms']], ['Matricule', $s['matricule']],
        ['Sous-comité', $s['anyama'] . ' - ' . $s['section']], ['Dortoir', $s['dortoir']], ['Test d\'entrée', $test],
    ];
    $tx = $px + $pw + 30; $ty = $y + 34;
    foreach ($champs as [$lib, $val]) {
        pdfEcrire($im, 17, $tx, $ty, $gris, 'tinos-latin-400-normal.ttf', $lib . ' :');
        $taille = 21; $maxw = $W - 46 - ($tx + 182);
        while ($taille > 14 && pdfLargeurTexte($taille, 'tinos-latin-700-normal.ttf', (string)$val) > $maxw) $taille -= 1;
        pdfEcrire($im, $taille, $tx + 182, $ty, $noir, 'tinos-latin-700-normal.ttf', (string)$val);
        $ty += 38;
    }
    $y += $ph + 22;

    // notes
    $st = $pdo->prepare("SELECT m.nom, m.note_max, n.note FROM matieres m LEFT JOIN notes n ON n.matiere_id = m.id AND n.seminariste_id = ? ORDER BY m.ordre, m.nom");
    $st->execute([$s['id']]);
    $lignes = $st->fetchAll();
    $somme = 0; $nb = 0;
    foreach ($lignes as $l) { if ($l['note'] !== null) { $somme += ((float)$l['note'] / (float)$l['note_max']) * 20; $nb++; } }
    $moy = $nb ? round($somme / $nb, 2) : null;

    $bas = $H - 225; // zone moyenne
    $dispo = $bas - $y - 8;
    $n = max(1, count($lignes)) + 1;
    $rh = (int)max(22, min(50, floor($dispo / $n)));
    $fs = $rh >= 44 ? 24 : ($rh >= 34 ? 21 : ($rh >= 28 ? 18 : 15));
    $x0 = 46; $x1 = $W - 46; $cols = [$x0, $x0 + 300, $x0 + 405, $x0 + 530, $x1];
    imagefilledrectangle($im, $x0, $y, $x1, $y + $rh, $vert);
    foreach (['Matière', 'Note', 'Barème', 'Appréciation'] as $i => $t) {
        pdfEcrire($im, $fs, $cols[$i] + 14, pdfBaseline('barlow-latin-700-normal.ttf', $fs, $y + $rh / 2), $blanc, 'barlow-latin-700-normal.ttf', $t);
    }
    $yy = $y + $rh;
    foreach ($lignes as $k => $l) {
        if ($k % 2 === 0) imagefilledrectangle($im, $x0, $yy, $x1, $yy + $rh, $teinte);
        imageline($im, $x0, $yy + $rh, $x1, $yy + $rh, $ligne);
        $cy = $yy + $rh / 2; $base = pdfBaseline('tinos-latin-700-normal.ttf', $fs, $cy);
        pdfEcrire($im, $fs, $cols[0] + 14, $base, $noir, 'tinos-latin-700-normal.ttf', $l['nom']);
        if ($l['note'] !== null) {
            [$app, $coul] = appreciationNote(((float)$l['note'] / (float)$l['note_max']) * 20);
            $c = $coul === 'vert' ? $vert : ($coul === 'or' ? $or : $rouge);
            pdfEcrire($im, $fs, $cols[1] + 14, $base, $noir, 'tinos-latin-700-normal.ttf', rtrim(rtrim(number_format((float)$l['note'], 2, ',', ''), '0'), ','));
            pdfEcrire($im, $fs, $cols[3] + 14, $base, $c, 'tinos-latin-700-normal.ttf', $app);
        } else {
            pdfEcrire($im, $fs, $cols[1] + 14, $base, $gris, 'tinos-latin-400-normal.ttf', '-');
            pdfEcrire($im, $fs, $cols[3] + 14, $base, $gris, 'tinos-latin-400-normal.ttf', 'Non noté');
        }
        pdfEcrire($im, $fs, $cols[2] + 14, $base, $gris, 'tinos-latin-400-normal.ttf', '/ ' . rtrim(rtrim(number_format((float)$l['note_max'], 2, ',', ''), '0'), ','));
        $yy += $rh;
    }
    if (!$lignes) { pdfEcrire($im, $fs, $x0 + 14, pdfBaseline('tinos-latin-400-normal.ttf', $fs, $yy + $rh / 2), $gris, 'tinos-latin-400-normal.ttf', 'Aucune matière définie.'); }

    // moyenne générale
    $by = $H - 200; $bh = 128;
    imagefilledrectangle($im, $x0, $by, $x1, $by + $bh, $orange);
    imagefilledrectangle($im, $x0, $by, $x0 + 8, $by + $bh, $vertF);
    $txt = $moy !== null ? 'Moyenne générale : ' . number_format($moy, 2, ',', ' ') . ' / 20' : 'Moyenne : en attente de notes';
    pdfTexteBoite($im, 1, 'barlow-latin-800-normal.ttf', 36, 0.9, $txt, $x0 + 10, $by + 14, $x1 - $x0 - 20, 56, $blanc, true);
    if ($moy !== null) {
        pdfTexteBoite($im, 1, 'barlow-latin-800-normal.ttf', 30, 0.9, 'Mention : ' . appreciationNote($moy)[0], $x0 + 10, $by + 70, $x1 - $x0 - 20, 48, $blanc, true);
    }
    $pied = EVENT_FULL . ' - bulletin généré automatiquement';
    $fw = pdfLargeurTexte(15, 'tinos-latin-400-normal.ttf', $pied);
    pdfEcrire($im, 15, ($W - $fw) / 2, $H - 32, $gris, 'tinos-latin-400-normal.ttf', $pied);
    return $im;
}

/** Bulletins : 2 par feuille A4 paysage (côte à côte, format A5 portrait), repère de découpe au milieu. */
function pdfBulletinsA4(PDO $pdo, array $lignes, $nomFichier) {
    @set_time_limit(600);
    @ini_set('memory_limit', '512M');
    $pdf = new PdfFlux($nomFichier);
    $A4w = 841.89; $A4h = 595.28; $cw = $A4w / 2; $marge = 8;
    foreach (array_chunk($lignes, 2) as $groupe) {
        $images = [];
        foreach ($groupe as $i => $s) {
            $im = pdfBulletin($pdo, $s);
            $wpx = imagesx($im); $hpx = imagesy($im);
            $jpeg = pdfJpeg($im, 90); imagedestroy($im);
            $h = $A4h - 2 * $marge; $w = $h * $wpx / $hpx;
            if ($w > $cw - 2 * $marge) { $w = $cw - 2 * $marge; $h = $w * $hpx / $wpx; }
            $images[] = [$jpeg, $wpx, $hpx, $i * $cw + ($cw - $w) / 2, ($A4h - $h) / 2, $w, $h];
        }
        $pdf->page($A4w, $A4h, $images, count($groupe) > 1 ? [[$cw, 0, $cw, $A4h]] : []);
    }
    if (!$lignes) { $pdf->page($A4w, $A4h, []); }
    $pdf->fin();
    exit;
}

/* ------------------------------------------------------------------ */
/*  Rapports journaliers : PDF A4 portrait (pagination automatique)      */
/* ------------------------------------------------------------------ */
class PdfRapport {
    private $W = 1654; private $H = 2339; // A4 à 200 dpi
    private $im; private $y; private $pdf; private $page = 0; private $titre; private $sous;
    private $c = [];
    const MARGE = 120;

    public function __construct(PdfFlux $pdf, $titre, $sousTitre) {
        $this->pdf = $pdf; $this->titre = $titre; $this->sous = $sousTitre;
        $this->nouvellePage();
    }
    private function couleurs() {
        $i = $this->im;
        $this->c = ['vertF' => imagecolorallocate($i, 6, 83, 47), 'vert' => imagecolorallocate($i, 11, 138, 78), 'orange' => imagecolorallocate($i, 247, 127, 0),
            'noir' => imagecolorallocate($i, 30, 38, 33), 'gris' => imagecolorallocate($i, 100, 112, 105), 'ligne' => imagecolorallocate($i, 214, 226, 219),
            'teinte' => imagecolorallocate($i, 235, 247, 240), 'blanc' => imagecolorallocate($i, 255, 255, 255)];
    }
    private function nouvellePage() {
        if ($this->im) { $this->cloturer(); }
        $this->im = pdfToile($this->W, $this->H);
        imageantialias($this->im, true);
        $this->couleurs();
        $this->page++;
        $c = $this->c; $W = $this->W;
        imagefilledrectangle($this->im, 0, 0, (int)($W / 3), 12, $c['orange']);
        imagefilledrectangle($this->im, (int)($W * 2 / 3), 0, $W, 12, $c['vert']);
        if ($this->page === 1) {
            $ent = imagecreatefrompng(__DIR__ . '/../assets/img/bulletin_entete.png');
            $ew = 900; $eh = (int)round($ew * imagesy($ent) / imagesx($ent));
            imagecopyresampled($this->im, $ent, (int)(($W - $ew) / 2), 50, 0, 0, $ew, $eh, imagesx($ent), imagesy($ent));
            imagedestroy($ent);
            $this->y = 50 + $eh + 30;
            pdfTexteBoite($this->im, 1, 'barlow-latin-800-normal.ttf', 54, 0.92, $this->titre, self::MARGE, $this->y, $W - 2 * self::MARGE, 70, $c['vertF'], true, 2);
            $this->y += 78;
            if ($this->sous !== '') {
                pdfTexteBoite($this->im, 1, 'tinos-latin-400-normal.ttf', 30, 0.92, $this->sous, self::MARGE, $this->y, $W - 2 * self::MARGE, 44, $c['gris'], true);
                $this->y += 56;
            }
            imagefilledrectangle($this->im, (int)($W / 2 - 220), $this->y, (int)($W / 2 - 1), $this->y + 6, $c['orange']);
            imagefilledrectangle($this->im, (int)($W / 2), $this->y, (int)($W / 2 + 220), $this->y + 6, $c['vert']);
            $this->y += 50;
        } else {
            pdfEcrire($this->im, 24, self::MARGE, 70, $c['gris'], 'tinos-latin-400-normal.ttf', $this->titre . ($this->sous !== '' ? ' - ' . $this->sous : ''));
            $this->y = 120;
        }
    }
    private function cloturer() {
        $c = $this->c;
        $pied = (defined('EVENT_FULL') ? EVENT_FULL : 'JOSPIA') . ' - page ' . $this->page;
        $fw = pdfLargeurTexte(22, 'tinos-latin-400-normal.ttf', $pied);
        pdfEcrire($this->im, 22, ($this->W - $fw) / 2, $this->H - 60, $c['gris'], 'tinos-latin-400-normal.ttf', $pied);
        $this->pdf->page(595.28, 841.89, [[pdfJpeg($this->im, 88), $this->W, $this->H, 0, 0, 595.28, 841.89]]);
        imagedestroy($this->im);
        $this->im = null;
    }
    private function place($h) {
        if ($this->y + $h > $this->H - 130) { $this->nouvellePage(); }
    }
    /** Découpe un texte (avec retours à la ligne) en lignes tenant dans $largeur. */
    private function lignes($texte, $police, $taille, $largeur) {
        $res = [];
        foreach (preg_split('/\R/u', (string)$texte) as $para) {
            $mots = preg_split('/\s+/u', trim($para), -1, PREG_SPLIT_NO_EMPTY);
            if (!$mots) { $res[] = ''; continue; }
            $ligne = '';
            foreach ($mots as $m) {
                $test = $ligne === '' ? $m : $ligne . ' ' . $m;
                if (pdfLargeurTexte($taille, $police, $test) <= $largeur) { $ligne = $test; }
                else { if ($ligne !== '') $res[] = $ligne; $ligne = $m; }
            }
            $res[] = $ligne;
        }
        return $res;
    }
    private $nb = 0;
    private function dateFr($d) {
        $m = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        $t = strtotime($d);
        return (int)date('j', $t) . ((int)date('j', $t) === 1 ? 'er' : '') . ' ' . $m[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
    }
    /** Écrit un paragraphe justifié (dernière ligne alignée à gauche). */
    private function paragrapheJustifie($texte, $taille, $x, $largeur, $retrait = 0, $interligne = 1.55) {
        $police = 'tinos-latin-400-normal.ttf'; $pas = (int)round($taille * 1.5 * $interligne / 1.5 * 1.0);
        foreach (preg_split('/\R/u', (string)$texte) as $para) {
            $mots = preg_split('/\s+/u', trim($para), -1, PREG_SPLIT_NO_EMPTY);
            if (!$mots) { $this->y += (int)($pas / 2); continue; }
            $lignes = []; $cur = []; $premiere = true;
            foreach ($mots as $m) {
                $lw = $largeur - ($premiere ? $retrait : 0);
                $test = $cur ? implode(' ', $cur) . ' ' . $m : $m;
                if ($cur && pdfLargeurTexte($taille, $police, $test) > $lw) { $lignes[] = [$cur, $premiere]; $cur = [$m]; $premiere = false; }
                else { $cur[] = $m; }
            }
            if ($cur) { $lignes[] = [$cur, $premiere]; }
            $n = count($lignes);
            foreach ($lignes as $i => [$ws, $prem]) {
                $this->place($pas + 10);
                $x0 = $x + ($prem ? $retrait : 0); $lw = $largeur - ($prem ? $retrait : 0);
                $base = $this->y + $taille;
                if ($i < $n - 1 && count($ws) > 1) {
                    $tot = 0; $wl = [];
                    foreach ($ws as $w) { $l = pdfLargeurTexte($taille, $police, $w); $wl[] = $l; $tot += $l; }
                    $gap = ($lw - $tot) / (count($ws) - 1); $cx = $x0;
                    foreach ($ws as $k => $w) { pdfEcrire($this->im, $taille, $cx, $base, $this->c['noir'], $police, $w); $cx += $wl[$k] + $gap; }
                } else {
                    pdfEcrire($this->im, $taille, $x0, $base, $this->c['noir'], $police, implode(' ', $ws));
                }
                $this->y += $pas;
            }
            $this->y += (int)($pas * 0.35);
        }
    }
    /** Un rapport = une lettre (une nouvelle page pour chaque rapport suivant). */
    public function rapport(array $r, $avecCommission = false) {
        $W = $this->W; $m = 170; $lw = $W - 2 * $m;
        if ($this->nb++ > 0) { $this->nouvellePage(); }
        $c = $this->c; $f = 'tinos-latin-400-normal.ttf'; $fb = 'tinos-latin-700-normal.ttf';
        // lieu et date, à droite
        $lieu = 'Abidjan, le ' . $this->dateFr($r['created_at'] ?: $r['date_rapport']);
        pdfEcrire($this->im, 30, $W - $m - pdfLargeurTexte(30, $f, $lieu), $this->y + 30, $c['noir'], $f, $lieu);
        $this->y += 70;
        // expéditeur / destinataire
        $com = (string)$r['commission'];
        pdfEcrire($this->im, 30, $m, $this->y + 30, $c['vertF'], $fb, 'Commission ' . $com);
        $this->y += 42;
        $admin = normaliserCommission($com) === 'ADMINISTRATION';
        if (!$admin) { pdfEcrire($this->im, 28, $m, $this->y + 28, $c['gris'], $f, 'Responsable : ' . ($r['auteur_nom'] ?: '-')); }
        $this->y += 70;
        $dest = ['À l\'attention de', 'Monsieur le Responsable du Comité d\'organisation', 'JOSPIA'];
        $dx = (int)($W / 2) - 20;
        pdfEcrire($this->im, 26, $dx, $this->y + 26, $c['gris'], $f, $dest[0]);
        pdfEcrire($this->im, 28, $dx, $this->y + 70, $c['noir'], $fb, 'Messieurs les Managers généraux');
        $this->y += 160;
        // objet
        $obj = 'Objet : Rapport journalier du ' . $this->dateFr($r['date_rapport']);
        imagefilledrectangle($this->im, $m, $this->y, $m + 8, $this->y + 56, $c['orange']);
        pdfEcrire($this->im, 32, $m + 26, pdfBaseline('barlow-latin-700-normal.ttf', 32, $this->y + 28), $c['vertF'], 'barlow-latin-700-normal.ttf', $obj);
        $this->y += 56 + 14;
        imageline($this->im, $m, $this->y, $W - $m, $this->y, $c['ligne']);
        $this->y += 40;
        pdfEcrire($this->im, 30, $m, $this->y + 30, $c['noir'], $f, 'Messieurs,');
        $this->y += 66;
        $this->paragrapheJustifie('Nous avons l\'honneur de vous rendre compte du déroulement des activités de la commission ' . $com . ' pour la journée du ' . $this->dateFr($r['date_rapport']) . '.', 30, $m, $lw, 90);
        $this->y += 10;
        foreach ([['1. Activités réalisées', 'activites'], ['2. Difficultés rencontrées', 'difficultes'], ['3. Prévisions et besoins', 'previsions']] as [$lib, $cle]) {
            $t = trim((string)($r[$cle] ?? ''));
            if ($t === '') continue;
            $this->place(150);
            pdfEcrire($this->im, 31, $m, $this->y + 31, $c['vertF'], $fb, $lib);
            $this->y += 58;
            $this->paragrapheJustifie($t, 30, $m, $lw, 90);
            $this->y += 8;
        }
        $this->y += 10;
        $this->paragrapheJustifie('Veuillez agréer, Messieurs, l\'expression de nos salutations distinguées.', 30, $m, $lw, 90);
        $this->place(170);
        $this->y += 24;
        $sig = (string)($r['auteur_nom'] ?: ('Commission ' . $com));
        $sx = $W - $m - 700;
        pdfEcrire($this->im, 28, $sx, $this->y + 28, $c['gris'], $f, 'Le responsable de la commission ' . $com . ',');
        if (!$admin) {
            pdfEcrire($this->im, 32, $sx, $this->y + 110, $c['noir'], $fb, $sig);
            imagefilledrectangle($this->im, $sx, $this->y + 124, $sx + 520, $this->y + 128, $c['orange']);
        } else { imagefilledrectangle($this->im, $sx, $this->y + 48, $sx + 520, $this->y + 52, $c['orange']); }
        $this->y += 150;
    }
    public function fin() {
        if (!$this->im) return;
        $this->cloturer();
    }
}

/** Télécharge un PDF de rapports : $rapports = lignes de rapports_journaliers (chaque ligne contient 'commission'). */
function pdfRapportsDocument(array $rapports, $titre, $sousTitre, $nomFichier, $avecCommission = false) {
    @set_time_limit(300);
    @ini_set('memory_limit', '512M');
    $pdf = new PdfFlux($nomFichier);
    $doc = new PdfRapport($pdf, $titre, $sousTitre);
    foreach ($rapports as $r) { $doc->rapport($r, $avecCommission); }
    if (!$rapports) { /* page vide avec message */ }
    $doc->fin();
    $pdf->fin();
    exit;
}
