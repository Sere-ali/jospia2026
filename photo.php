<?php
/** Sert une photo depuis la base quand le fichier n'existe plus sur le disque (redéploiement). */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';
$nom = $_GET['f'] ?? '';
if (!preg_match('/^[A-Za-z0-9._-]{1,120}$/', $nom)) { http_response_code(404); exit; }
try {
    photoTableBase($pdo);
    $st = $pdo->prepare("SELECT mime, donnees FROM photos_stockees WHERE nom = ?");
    $st->execute([$nom]);
    $p = $st->fetch();
} catch (Throwable $e) { $p = false; }
if (!$p) { http_response_code(404); exit; }
// Remet le fichier sur le disque pour les requêtes suivantes
$dossier = __DIR__ . '/uploads/photos';
if (is_dir($dossier) && is_writable($dossier)) { @file_put_contents($dossier . '/' . $nom, $p['donnees']); }
header('Content-Type: ' . $p['mime']);
header('Cache-Control: public, max-age=86400');
echo $p['donnees'];
