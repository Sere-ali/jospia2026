<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/pdf_docs.php';
exigerRole(['seminariste', 'admin', 'superadmin']);
$u = utilisateurCourant();
$id = $u['role'] === 'seminariste' ? (int)$u['seminariste_id'] : (int)($_GET['id'] ?? 0);
$st = $pdo->prepare("SELECT * FROM seminaristes WHERE id = ?"); $st->execute([$id]);
$s = $st->fetch();
if (!$s) { http_response_code(404); die('Fiche introuvable.'); }
if ($u['role'] === 'seminariste') {
    // Le séminariste ne télécharge sa fiche qu'après validation de son paiement par la commission Finance.
    $stV = $pdo->prepare("SELECT COUNT(*) FROM paiements WHERE seminariste_id = ? AND statut = 'validé'");
    $stV->execute([$id]);
    if ((int)$stV->fetchColumn() < 1) { http_response_code(403); die("Votre fiche d'inscription sera disponible dès que la commission Finance aura validé votre paiement."); }
}
pdfFiche($pdo, $s, 'fiche_' . preg_replace('/[^A-Za-z0-9_-]/', '', (string)$s['matricule']) . '.pdf');
