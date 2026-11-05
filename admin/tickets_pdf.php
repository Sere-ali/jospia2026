<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['finance', 'admin', 'superadmin']);
require_once __DIR__ . '/../includes/pdf_docs.php';
ticketsPreparer($pdo);
$de = max(1, (int)($_GET['de'] ?? 1));
$a = max($de, (int)($_GET['a'] ?? $de));
if ($a - $de > 399) { $a = $de + 399; }
$st = $pdo->prepare("SELECT numero, code FROM tickets WHERE numero BETWEEN ? AND ? ORDER BY numero");
$st->execute([$de, $a]);
$liste = $st->fetchAll();
if (!$liste) { http_response_code(404); die('Aucun ticket dans cette plage.'); }
pdfTicketsA4($liste, 'tickets_jospia_' . $liste[0]['numero'] . '_a_' . $liste[count($liste) - 1]['numero'] . '.pdf');
