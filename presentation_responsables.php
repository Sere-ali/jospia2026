<?php
// Présentation des responsables de commission : page publique en lecture seule.
define('PRESENTATION_PUBLIQUE', true);
header('X-Robots-Tag: noindex, nofollow');
require __DIR__ . '/admin/presentation_responsables.php';
