<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'superadmin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = ($_POST['ouvrir'] ?? '0') === '1' ? '1' : '0';
    $pdo->exec("CREATE TABLE IF NOT EXISTS parametres (cle VARCHAR(50) PRIMARY KEY, valeur VARCHAR(255) NOT NULL) ENGINE=InnoDB");
    $pdo->prepare("INSERT INTO parametres (cle, valeur) VALUES ('test_ouvert', ?) ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)")->execute([$v]);
    $_SESSION['flash_succes'] = $v === '1' ? "Test d'entrée déverrouillé." : "Test d'entrée verrouillé.";
}
redirect(estSuperAdmin() && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'dashboard') !== false ? '/admin/dashboard' : '/admin/commission_scientifique');
