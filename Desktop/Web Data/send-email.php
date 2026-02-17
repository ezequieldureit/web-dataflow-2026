<?php
// Cargar variables de entorno desde .env
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Cargar .env si existe
loadEnv(__DIR__ . '/.env');

// Configuración SMTP - Desde variables de entorno
$smtp_host = getenv('SMTP_HOST') ?: 'mail.dataflow-services.com';
$smtp_port = (int)(getenv('SMTP_PORT') ?: 587);
$smtp_username = getenv('SMTP_USERNAME') ?: 'info@dataflow-services.com';
$smtp_password = getenv('SMTP_PASSWORD') ?: '';
$smtp_encryption = getenv('SMTP_ENCRYPTION') ?: 'tls';

// Configuración de email
$to_email = getenv('TO_EMAIL') ?: 'info@dataflow-services.com';
$from_email = getenv('FROM_EMAIL') ?: 'info@dataflow-services.com';
$from_name = getenv('FROM_NAME') ?: 'Dataflow Services Web';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
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

// Verificar Cloudflare Turnstile
$turnstile_secret = getenv('TURNSTILE_SECRET_KEY') ?: '';
if (empty($turnstile_secret)) {
    // Log del error para debugging (solo en desarrollo)
    error_log('Turnstile: Secret key no configurada. Verifica el archivo .env');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de configuración del servidor. Por favor contacta al administrador.']);
    exit;
}
$turnstile_token = isset($_POST['cf-turnstile-response']) ? trim($_POST['cf-turnstile-response']) : '';

if (empty($turnstile_token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Por favor completá la verificación de seguridad.']);
    exit;
}

// Verificar token con Cloudflare
$turnstile_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$turnstile_data = array(
    'secret' => $turnstile_secret,
    'response' => $turnstile_token,
    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
);

$turnstile_options = array(
    'http' => array(
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($turnstile_data)
    )
);

$turnstile_context = stream_context_create($turnstile_options);
$turnstile_result = @file_get_contents($turnstile_url, false, $turnstile_context);

if ($turnstile_result === false) {
    error_log('Turnstile: Error al conectar con la API de Cloudflare');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Error al verificar la seguridad. Por favor intentá nuevamente.']);
    exit;
}

$turnstile_response = json_decode($turnstile_result, true);

if (!$turnstile_response || !isset($turnstile_response['success']) || !$turnstile_response['success']) {
    $error_codes = isset($turnstile_response['error-codes']) ? implode(', ', $turnstile_response['error-codes']) : 'desconocido';
    error_log('Turnstile: Verificación fallida. Códigos de error: ' . $error_codes);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Verificación de seguridad fallida. Por favor intentá nuevamente.']);
    exit;
}

// Preparar el asunto
$subject = 'Nuevo contacto desde Dataflow Services';

// Preparar el cuerpo del email
$email_body = "Nuevo mensaje de contacto desde la web de Dataflow Services\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "Nombre: " . $name . "\n";
$email_body .= "Email: " . $email . "\n";
$email_body .= "Teléfono: " . ($phone ? $phone : 'No proporcionado') . "\n";
$email_body .= "Servicio: " . $service . "\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
$email_body .= "Mensaje:\n" . $message . "\n\n";
$email_body .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Función para enviar email usando SMTP
function sendEmailSMTP($to, $subject, $body, $from_email, $from_name, $reply_to, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_encryption) {
    // Crear conexión SMTP
    $socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
    
    if (!$socket) {
        return false;
    }
    
    // Leer respuesta inicial
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) != '220') {
        fclose($socket);
        return false;
    }
    
    // EHLO
    fputs($socket, "EHLO " . $smtp_host . "\r\n");
    $response = fgets($socket, 515);
    
    // STARTTLS si es necesario
    if ($smtp_encryption == 'tls' && $smtp_port == 587) {
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        fputs($socket, "EHLO " . $smtp_host . "\r\n");
        $response = fgets($socket, 515);
    }
    
    // Autenticación
    fputs($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_user) . "\r\n");
    $response = fgets($socket, 515);
    
    fputs($socket, base64_encode($smtp_pass) . "\r\n");
    $response = fgets($socket, 515);
    
    if (substr($response, 0, 3) != '235') {
        fclose($socket);
        return false;
    }
    
    // MAIL FROM
    fputs($socket, "MAIL FROM: <" . $from_email . ">\r\n");
    $response = fgets($socket, 515);
    
    // RCPT TO
    fputs($socket, "RCPT TO: <" . $to . ">\r\n");
    $response = fgets($socket, 515);
    
    // DATA
    fputs($socket, "DATA\r\n");
    $response = fgets($socket, 515);
    
    // Headers
    $headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $reply_to . "\r\n";
    $headers .= "To: <" . $to . ">\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "\r\n";
    
    // Enviar email
    fputs($socket, $headers . $body . "\r\n.\r\n");
    $response = fgets($socket, 515);
    
    // QUIT
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    
    return substr($response, 0, 3) == '250';
}

// Headers del email
$headers = "From: " . $from_name . " <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Intentar enviar el email usando mail() (más confiable en la mayoría de hostings)
$email_sent = @mail($to_email, $subject, $email_body, $headers);

// Si mail() falla, intentar con SMTP
if (!$email_sent) {
    $email_sent = sendEmailSMTP($to_email, $subject, $email_body, $from_email, $from_name, $email, $smtp_host, $smtp_port, $smtp_username, $smtp_password, $smtp_encryption);
}

// Responder según el resultado
if ($email_sent) {
    // Éxito
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => '¡Gracias por contactarnos! Tu mensaje fue enviado correctamente. Te responderemos en menos de 24 horas.'
    ]);
} else {
    // Error
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Hubo un error al enviar el mensaje. Por favor intentá nuevamente o contactanos por WhatsApp.'
    ]);
}
?>

