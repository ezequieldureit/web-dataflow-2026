<?php
/**
 * Script de prueba para enviar emails localmente
 * Este script simula el envío sin requerir SMTP real
 */

header('Content-Type: application/json');

// Simular verificación de Turnstile (solo para pruebas locales)
$turnstile_token = isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '';

if (empty($turnstile_token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Por favor completá la verificación de seguridad.']);
    exit;
}

// Obtener datos del formulario
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$service = isset($_POST['service']) ? trim($_POST['service']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Validar campos obligatorios
if (empty($name) || empty($email) || empty($service) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Por favor completá todos los campos obligatorios']);
    exit;
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El email no es válido']);
    exit;
}

// En modo prueba, solo mostrar los datos en consola/log
$email_body = "=== PRUEBA LOCAL - Formulario de Contacto ===\n\n";
$email_body .= "Nombre: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Teléfono: " . ($phone ?: 'No proporcionado') . "\n";
$email_body .= "Servicio: $service\n\n";
$email_body .= "Mensaje:\n$message\n\n";
$email_body .= "Token Turnstile: " . substr($turnstile_token, 0, 20) . "...\n";
$email_body .= "Fecha: " . date('Y-m-d H:i:s') . "\n";

// Guardar en archivo de log para pruebas
$log_file = __DIR__ . '/test-email-log.txt';
file_put_contents($log_file, $email_body . "\n---\n\n", FILE_APPEND);

// Simular éxito
echo json_encode([
    'success' => true,
    'message' => '¡Mensaje de prueba enviado correctamente! (Modo local - ver test-email-log.txt)'
]);
