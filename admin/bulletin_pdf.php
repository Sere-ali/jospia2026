<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);
require_once __DIR__ . '/../includes/pdf_docs.php';

$ids = array_values(array_filter(array_map('intval', explode(',', (string)($_GET['ids'] ?? ($_GET['id'] ?? ''))))));
if (!empty($_GET['tous'])) {
    $niveau = $_GET['niveau'] ?? '';
    $sql = "SELECT * FROM seminaristes" . ($niveau !== '' ? " WHERE niveau_affecte = ?" : "") . " ORDER BY niveau_affecte, nom_prenoms";
    $st = $pdo->prepare($sql); $st->execute($niveau !== '' ? [$niveau] : []);
    $l = $st->fetchAll();
    $nom = 'bulletins' . ($niveau !== '' ? '_' . preg_replace('/[^A-Za-z0-9]+/', '_', $niveau) : '') . '.pdf';
} else {
    if (!$ids) die('Séminariste introuvable.');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT * FROM seminaristes WHERE id IN ($in) ORDER BY nom_prenoms"); $st->execute($ids);
    $l = $st->fetchAll();
    if (!$l) die('Séminariste introuvable.');
    $nom = 'bulletin_' . preg_replace('/[^A-Za-z0-9]+/', '_', $l[0]['nom_prenoms']) . '.pdf';
}
pdfBulletinsA4($pdo, $l, $nom);
