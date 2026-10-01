<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['admin', 'superadmin', 'mg']);
require_once __DIR__ . '/../includes/pdf_docs.php';
date_default_timezone_set('Africa/Abidjan');
rapportsPreparer($pdo);

$commission = trim($_GET['commission'] ?? '');
$jour = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['jour'] ?? '') ? $_GET['jour'] : '';
$id = (int)($_GET['id'] ?? 0);

$where = ['1=1']; $params = [];
if ($id) { $where[] = 'id = ?'; $params[] = $id; }
if ($commission !== '') { $where[] = 'commission = ?'; $params[] = $commission; }
if ($jour !== '') { $where[] = 'date_rapport = ?'; $params[] = $jour; }
$st = $pdo->prepare("SELECT * FROM rapports_journaliers WHERE " . implode(' AND ', $where) . " ORDER BY " . ($commission === '' && !$id ? "commission, " : "") . "date_rapport DESC, id DESC LIMIT 500");
$st->execute($params);
$rapports = $st->fetchAll();
if (!$rapports) { die("Aucun rapport à télécharger."); }

$com = $commission !== '' ? $commission : ($id ? $rapports[0]['commission'] : '');
$titre = $com !== '' ? 'RAPPORTS JOURNALIERS - ' . mb_strtoupper(nomCommissionComplet($com), 'UTF-8') : 'RAPPORTS JOURNALIERS DES COMMISSIONS';
$sous = $jour !== '' ? 'Journée du ' . date('d/m/Y', strtotime($jour)) : 'Édition ' . (defined('EVENT_NAME') ? EVENT_NAME : '');
$fichier = 'rapports_' . ($com !== '' ? preg_replace('/[^A-Za-z0-9]+/', '_', normaliserCommission($com)) : 'toutes_commissions') . ($jour !== '' ? '_' . $jour : '') . '.pdf';
pdfRapportsDocument($rapports, $titre, $sous, $fichier, $com === '');
