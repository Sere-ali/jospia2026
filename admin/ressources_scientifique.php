<?php
require_once __DIR__ . '/../includes/init.php';
exigerRole(['scientifique', 'admin', 'superadmin']);
require_once __DIR__ . '/../includes/ressources_sci.php';
resTraiterTelechargement();
$titrePage = "Archives";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/admin_nav.php';
resAfficher(BASE_URL . '/admin/ressources_scientifique');
require_once __DIR__ . '/../includes/footer.php';
