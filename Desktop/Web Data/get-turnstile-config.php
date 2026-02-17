<?php
/**
 * Endpoint para obtener la clave pública de Turnstile
 * Esta clave es pública y puede ser expuesta
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$siteKey = TURNSTILE_SITE_KEY;

if (empty($siteKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Turnstile site key no configurada']);
    exit;
}

echo json_encode(['siteKey' => $siteKey]);
