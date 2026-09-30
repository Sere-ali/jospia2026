<?php
require_once __DIR__ . '/config/db.php';
$sql = file_get_contents(__DIR__ . '/sql/migration_features.sql');
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec($sql);
    echo "Migration SQL exécutée avec succès !";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
unlink(__FILE__);
