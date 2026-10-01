<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/pdf_docs.php';
exigerRole(['seminariste', 'admin', 'superadmin', 'finance']);
$u = utilisateurCourant();
$id = (int)($_GET['id'] ?? 0);
$sql = "SELECT p.*, c.nom_affiche AS valideur FROM paiements p LEFT JOIN comptes c ON c.id = p.admin_validateur_id WHERE p.id = ? AND p.statut = 'validé'";
$par = [$id];
if ($u['role'] === 'seminariste') { $sql .= " AND p.seminariste_id = ?"; $par[] = (int)$u['seminariste_id']; }
$st = $pdo->prepare($sql); $st->execute($par);
$recu = $st->fetch();
if (!$recu) { http_response_code(404); die('Reçu introuvable.'); }
$st = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?"); $st->execute([$recu['seminariste_id']]);
$s = $st->fetch();
if (!$s) { http_response_code(404); die('Séminariste introuvable.'); }
pdfRecu($s, $recu, 'recu_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$s['matricule']) . '.pdf');
