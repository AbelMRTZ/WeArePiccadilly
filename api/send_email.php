<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';
require_once __DIR__ . '/config.php';

$is_local = str_starts_with($_SERVER['HTTP_HOST'] ?? 'localhost', 'localhost')
         || ($_SERVER['HTTP_HOST'] ?? '') === '127.0.0.1';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Acceso no permitido.']);
    exit;
}

// Validación y longitud máxima
$name    = trim(htmlspecialchars($_POST['nombre']  ?? '', ENT_QUOTES, 'UTF-8'));
$email   = trim(htmlspecialchars($_POST['email']   ?? '', ENT_QUOTES, 'UTF-8'));
$asunto  = trim(htmlspecialchars($_POST['asunto']  ?? '', ENT_QUOTES, 'UTF-8'));
$message = trim(htmlspecialchars($_POST['mensaje'] ?? '', ENT_QUOTES, 'UTF-8'));

if (empty($name) || empty($email) || empty($message) || empty($asunto)) {
    http_response_code(400);
    echo json_encode(['error' => 'Todos los campos son obligatorios.']);
    exit;
}

if (strlen($name) > 100 || strlen($asunto) > 200 || strlen($message) > 2000 || strlen($email) > 150) {
    http_response_code(400);
    echo json_encode(['error' => 'Algún campo supera la longitud máxima permitida.']);
    exit;
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'El formato del correo electrónico no es válido.']);
    exit;
}

// Rate limiting simple por sesión
session_start();
$now = time();
if (isset($_SESSION['last_email_sent']) && ($now - $_SESSION['last_email_sent']) < 60) {
    http_response_code(429);
    echo json_encode(['error' => 'Por favor, espera un momento antes de enviar otro mensaje.']);
    exit;
}

// Modo local: simular éxito sin enviar SMTP
if ($is_local) {
    $_SESSION['last_email_sent'] = $now;
    echo json_encode(['message' => '[LOCAL] Formulario recibido. En producción se enviará el correo.']);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom(SMTP_USER, 'WeArePiccadilly');
    $mail->addReplyTo($_POST['email'], $name);
    $mail->addAddress(SMTP_TO);

    $mail->isHTML(true);
    $mail->Subject = $asunto;
    $mail->Body    = "<b>Nombre:</b> $name<br><b>Correo:</b> $email<br><b>Mensaje:</b><br>$message";
    $mail->AltBody = "Nombre: $name\nCorreo: $email\n\nMensaje:\n$message";

    $mail->send();
    $_SESSION['last_email_sent'] = $now;
    echo json_encode(['message' => 'Correo enviado correctamente.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al enviar el correo. Inténtalo de nuevo más tarde.']);
}
