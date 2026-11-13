<?php
/** Lien signé /archives/SIGNATURE/chemin/fichier.docx : permet à Word, Excel et PowerPoint d'ouvrir un fichier des archives. */
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/ressources_sci.php';
$chemin = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$pref = BASE_URL . '/archives/';
if (strpos($chemin, $pref) !== 0) { http_response_code(404); die('Introuvable.'); }
$parts = explode('/', substr($chemin, strlen($pref)), 2);
$sig = $parts[0] ?? ''; $rel = rawurldecode($parts[1] ?? '');
if ($rel === '' || !hash_equals(resSignature($rel), $sig)) { http_response_code(403); die('Lien invalide.'); }
resTelecharger($rel, 'download');
