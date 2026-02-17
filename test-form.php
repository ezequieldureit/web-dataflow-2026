<?php
/**
 * Script de prueba para diagnosticar problemas del formulario
 */

echo "<h1>Diagnóstico del Formulario de Contacto</h1>";
echo "<pre>";

// 1. Verificar PHP
echo "✓ PHP está funcionando\n";
echo "Versión PHP: " . phpversion() . "\n\n";

// 2. Verificar archivos
$files = [
    'config.php',
    'send-email.php',
    'get-turnstile-config.php',
    '.env'
];

echo "=== Archivos ===\n";
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? "✓" : "✗") . " $file " . ($exists ? "existe" : "NO existe") . "\n";
}
echo "\n";

// 3. Verificar .env
echo "=== Variables de Entorno ===\n";
if (file_exists(__DIR__ . '/.env')) {
    require_once __DIR__ . '/config.php';
    $vars = [
        'TURNSTILE_SITE_KEY',
        'TURNSTILE_SECRET_KEY',
        'SMTP_HOST',
        'SMTP_USERNAME',
        'TO_EMAIL'
    ];
    foreach ($vars as $var) {
        $value = getenv($var);
        if ($var === 'TURNSTILE_SECRET_KEY' || $var === 'SMTP_PASSWORD') {
            $value = $value ? '***CONFIGURADA***' : 'NO CONFIGURADA';
        }
        echo ($value ? "✓" : "✗") . " $var: " . ($value ?: 'NO CONFIGURADA') . "\n";
    }
} else {
    echo "✗ Archivo .env no encontrado\n";
}
echo "\n";

// 4. Verificar permisos
echo "=== Permisos ===\n";
$writable = is_writable(__DIR__);
echo ($writable ? "✓" : "✗") . " Directorio escribible: " . ($writable ? "Sí" : "No") . "\n";
echo "\n";

// 5. Probar conexión SMTP (si está configurado)
echo "=== Prueba SMTP ===\n";
if (file_exists(__DIR__ . '/.env')) {
    require_once __DIR__ . '/config.php';
    $smtp_host = getenv('SMTP_HOST');
    $smtp_port = getenv('SMTP_PORT') ?: 587;
    
    if ($smtp_host) {
        echo "Intentando conectar a $smtp_host:$smtp_port...\n";
        $connection = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 5);
        if ($connection) {
            echo "✓ Conexión SMTP exitosa\n";
            fclose($connection);
        } else {
            echo "✗ Error de conexión SMTP: $errstr ($errno)\n";
        }
    } else {
        echo "⚠ SMTP_HOST no configurado\n";
    }
}
echo "\n";

// 6. Probar Turnstile
echo "=== Turnstile ===\n";
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    $siteKey = defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    if ($siteKey) {
        echo "✓ Site Key configurada: " . substr($siteKey, 0, 10) . "...\n";
    } else {
        echo "✗ Site Key NO configurada\n";
    }
    
    $secretKey = getenv('TURNSTILE_SECRET_KEY');
    if ($secretKey) {
        echo "✓ Secret Key configurada\n";
    } else {
        echo "✗ Secret Key NO configurada\n";
    }
}
echo "\n";

// 7. Verificar función mail()
echo "=== Función mail() ===\n";
if (function_exists('mail')) {
    echo "✓ Función mail() disponible\n";
} else {
    echo "✗ Función mail() NO disponible\n";
}
echo "\n";

echo "</pre>";
echo "<h2>Prueba del Formulario</h2>";
echo "<p><a href='contacto.html'>Ir al formulario de contacto</a></p>";
echo "<p><a href='get-turnstile-config.php'>Probar endpoint de Turnstile</a></p>";
?>
