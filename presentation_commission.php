<?php
// Présentation de la commission Administration : page publique en lecture seule (aucune connexion requise).
define('PRESENTATION_PUBLIQUE', true);
header('X-Robots-Tag: noindex, nofollow');
require __DIR__ . '/admin/presentation_administration.php';
