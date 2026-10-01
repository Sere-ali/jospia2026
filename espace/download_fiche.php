<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/pdf_docs.php';
exigerRole(['seminariste', 'admin', 'superadmin']);
$u = utilisateurCourant();
$id = $u['role'] === 'seminariste' ? (int)$u['seminariste_id'] : (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?"); $st->execute([$id]);
$s = $st->fetch();
if (!$s) { http_response_code(404); die('Fiche introuvable.'); }
pdfFiche($pdo, $s, 'fiche_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$s['matricule']) . '.pdf');
