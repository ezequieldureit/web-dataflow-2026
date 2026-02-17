<?php
/**
 * Endpoint para obtener la clave pública de Turnstile
 * Esta clave es pública y puede ser expuesta
 */

// Manejar errores silenciosamente
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/config.php';
    
    $siteKey = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    
    // Si no está definida, intentar leer directamente del .env
    if (empty($siteKey)) {
        $envFile = __DIR__ . '/.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, 'TURNSTILE_SITE_KEY=') === 0) {
                    $siteKey = trim(substr($line, strlen('TURNSTILE_SITE_KEY=')));
                    break;
                }
            }
        }
    }
    
    if (empty($siteKey)) {
        http_response_code(500);
        echo json_encode(['error' => 'Turnstile site key no configurada', 'siteKey' => '']);
        exit;
    }
    
    echo json_encode(['siteKey' => $siteKey]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar configuración', 'siteKey' => '']);
}
