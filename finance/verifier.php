<?php
/**
 * API de vérification d'un reçu (appelée par le scanner).
 * Retourne du JSON : {valide: bool, message, ...infos}
 */
require_once __DIR__ . '/../includes/init.php';
header('Content-Type: application/json; charset=utf-8');

if (!estConnecte() || !estFinance()) {
    http_response_code(403);
    echo json_encode(['valide' => false, 'message' => 'Accès refusé']);
    exit;
}

$code = trim($_POST['code'] ?? $_GET['code'] ?? '');
$code = preg_replace('/^JOSPIA:/i', '', $code);
if (!preg_match('/^[a-f0-9]{16,40}$/i', $code)) {
    echo json_encode(['valide' => false, 'message' => 'QR code non reconnu']);
    exit;
}

$st = $pdo->prepare("SELECT p.id, p.statut, p.montant, p.date_validation, s.nom_prenoms, s.matricule, s.photo, s.anyama, s.section, s.dortoir
                     FROM paiements p JOIN seminaristes s ON s.id = p.seminariste_id
                     WHERE p.code_recu = ? LIMIT 1");
$st->execute([$code]);
$r = $st->fetch();

if (!$r || $r['statut'] !== 'validé') {
    echo json_encode(['valide' => false, 'message' => 'Personne introuvable dans la base']);
    exit;
}

echo json_encode([
    'valide' => true,
    'message' => 'Paiement validé',
    'nom' => $r['nom_prenoms'],
    'matricule' => $r['matricule'],
    'section' => $r['anyama'] . ' - ' . $r['section'],
    'dortoir' => $r['dortoir'],
    'montant' => (int)$r['montant'],
    'date' => $r['date_validation'] ? date('d/m/Y H:i', strtotime($r['date_validation'])) : '',
    'photo' => $r['photo'] ? BASE_URL . '/uploads/photos/' . $r['photo'] : null,
]);
