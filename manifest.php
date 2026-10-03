<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/manifest+json; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$b = BASE_URL;
echo json_encode([
    'name' => 'JOSPIA 2026 - Journées Spirituelles Islamiques d\'Anyama',
    'short_name' => 'JOSPIA 2026',
    'description' => 'Inscriptions, espace personnel, badges et programme des JOSPIA 2026.',
    'id' => $b . '/',
    'start_url' => $b . '/?source=pwa',
    'scope' => $b . '/',
    'display' => 'standalone',
    'orientation' => 'portrait',
    'lang' => 'fr',
    'background_color' => '#ffffff',
    'theme_color' => '#0B8A4E',
    'icons' => [
        ['src' => $b . '/assets/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $b . '/assets/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => $b . '/assets/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
