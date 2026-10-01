<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['seminariste']);
require_once __DIR__ . '/../includes/pdf_docs.php';
$u = utilisateurCourant();
if (!resultatsPublies($pdo)) { die("Les résultats n'ont pas encore été publiés."); }
$st = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?");
$st->execute([$u['seminariste_id']]);
$s = $st->fetch();
if (!$s) { die('Fiche introuvable.'); }
pdfBulletinsA4($pdo, [$s], 'mon_bulletin.pdf');
