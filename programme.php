<?php
/** Sert le programme journalier publié (affichage ou téléchargement). */
require_once __DIR__ . '/includes/init.php';
try {
    programmePreparer($pdo);
    $id = (int)($_GET['id'] ?? 0);
    $st = $pdo->prepare("SELECT * FROM programmes_journaliers WHERE id = ?" . (estSuperAdmin() || estScientifique() ? '' : ' AND publie = 1'));
    $st->execute([$id]);
    $p = $st->fetch();
} catch (Throwable $e) { $p = false; }
if (!$p) { http_response_code(404); echo 'Programme introuvable.'; exit; }
$nom = preg_replace('/[^A-Za-z0-9._-]/', '_', $p['nom_fichier']);
header('Content-Type: ' . $p['mime']);
header('Content-Disposition: ' . (isset($_GET['dl']) ? 'attachment' : 'inline') . '; filename="' . $nom . '"');
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . strlen($p['donnees']));
echo $p['donnees'];
