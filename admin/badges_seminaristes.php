<?php
// 1. Définition du type de contenu renvoyé
header('Content-Type: image/png');

// 2. Chargement du template de base (le badge vide avec arrière-plan et logos)
// Assurez-vous d'avoir l'image "template_seminaristes.png" vide (sans texte ni photo) dans le dossier.
$templatePath = 'template_seminaristes.png';
if (!file_exists($templatePath)) {
    die("Erreur : Le fichier template '$templatePath' est introuvable.");
}
$badge = imagecreatefrompng($templatePath);

// Dimensions du template
$badgeWidth  = imagesx($badge);
$badgeHeight = imagesy($badge);

// 3. Configuration des couleurs (RGB)
$white = imagecolorallocate($badge, 255, 255, 255);

// 4. Données dynamiques du participant (à remplacer par des données de la BDD)
$nomComple     = "CHEICK OMER DIARRA";
$dortoir       = "Amir Mamadou Kone 2";
$niveau        = "Universitaire";
$matricule     = "JOS202614";
$photoPath     = "uploads/user_photo.jpg"; // Photo de l'utilisateur

// 5. Intégration de la photo du participant dans le cadre circulaire
if (file_exists($photoPath)) {
    // Adapter selon le format de la photo importée
    $photoSource = imagecreatefromjpeg($photoPath); 
    
    // Définir la taille et la position du cercle central sur le badge
    // (À ajuster en fonction des dimensions réelles de votre template_seminaristes.png)
    $circleX = 260;     // Position X de la photo
    $circleY = 380;     // Position Y de la photo
    $circleSize = 480;  // Diamètre du cadre circulaire

    // Création d'un masque circulaire avec transparence
    $mask = imagecreatetruecolor($circleSize, $circleSize);
    imagealphablending($mask, false);
    imagesavealpha($mask, true);
    
    $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
    $fillColor   = imagecolorallocate($mask, 0, 0, 0);
    
    imagefilledrectangle($mask, 0, 0, $circleSize, $circleSize, $transparent);
    imagefilledellipse($mask, $circleSize / 2, $circleSize / 2, $circleSize, $circleSize, $fillColor);

    // Redimensionnement de la photo source
    $resizedPhoto = imagecreatetruecolor($circleSize, $circleSize);
    imagecopyresampled($resizedPhoto, $photoSource, 0, 0, 0, 0, $circleSize, $circleSize, imagesx($photoSource), imagesy($photoSource));

    // Application du masque sur la photo
    for ($x = 0; $x < $circleSize; $x++) {
        for ($y = 0; $y < $circleSize; $y++) {
            $alpha = (imagecolorat($mask, $x, $y) >> 24) & 0x7F;
            $color = imagecolorat($resizedPhoto, $x, $y);
            $r = ($color >> 16) & 0xFF;
            $g = ($color >> 8) & 0xFF;
            $b = $color & 0xFF;
            $newColor = imagecolorallocatealpha($resizedPhoto, $r, $g, $b, $alpha);
            imagesetpixel($resizedPhoto, $x, $y, $newColor);
        }
    }

    // Superposition de la photo découpée sur le badge
    imagecopy($badge, $resizedPhoto, $circleX, $circleY, 0, 0, $circleSize, $circleSize);
    
    // Nettoyage des ressources mémoire de la photo
    imagedestroy($photoSource);
    imagedestroy($resizedPhoto);
    imagedestroy($mask);
}

// 6. Chemin vers la police TTF (ex: Montserrat, Montserrat-Bold)
// Assurez-vous que le dossier fonts et les polices existent
$fontBold = __DIR__ . '/fonts/Montserrat-Bold.ttf';

if (!file_exists($fontBold)) {
    die("Erreur : La police '$fontBold' est introuvable.");
}

// 7. Écriture des textes sur le badge

// --- NOM DU PARTICIPANT ---
$fontSizeNom = 32;
// Centrer horizontalement le nom
$bbox = imagettfbbox($fontSizeNom, 0, $fontBold, $nomComple);
$nomX = ($badgeWidth - ($bbox[2] - $bbox[0])) / 2;
imagettftext($badge, $fontSizeNom, 0, $nomX, 910, $white, $fontBold, $nomComple);

// --- DETAILS (Dortoir, Niveau, Matricule) ---
$fontSizeDetails = 20;
$startXLabel = 250;
$startXValue = 450;

// Dortoir
imagettftext($badge, $fontSizeDetails, 0, $startXLabel, 980, $white, $fontBold, "DORTOIR :");
imagettftext($badge, $fontSizeDetails, 0, $startXValue, 980, $white, $fontBold, $dortoir);

// Niveau
imagettftext($badge, $fontSizeDetails, 0, $startXLabel, 1030, $white, $fontBold, "NIVEAU :");
imagettftext($badge, $fontSizeDetails, 0, $startXValue, 1030, $white, $fontBold, $niveau);

// Matricule
imagettftext($badge, $fontSizeDetails, 0, $startXLabel, 1080, $white, $fontBold, "MATRICULE :");
imagettftext($badge, $fontSizeDetails, 0, $startXValue, 1080, $white, $fontBold, $matricule);

// 8. Génération et affichage de l'image finale
imagepng($badge);

// 9. Libération de la mémoire
imagedestroy($badge);
?>
