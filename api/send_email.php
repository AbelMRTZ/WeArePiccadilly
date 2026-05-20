<?php
// send_email.php

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../src/PHPMailer.php';
require_once __DIR__ . '/../src/SMTP.php';
require_once __DIR__ . '/../src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recoge y limpia los datos
    $name    = htmlspecialchars($_POST['nombre'] ?? '');
    $email   = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['mensaje'] ?? '');
    $asunto  = htmlspecialchars($_POST['asunto'] ?? '');

    // Validación simple
    if (empty($name) || empty($email) || empty($message) || empty($asunto)) {
        http_response_code(400); // Código HTTP de error
        echo json_encode(["error" => "Todos los campos son obligatorios."]);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'abelmartinezmolina5@gmail.com';  // ← Tu cuenta Gmail
        $mail->Password   = 'nbeh rppb ziah sycr';            // ← Tu clave de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom('abelmartinezmolina5@gmail.com', 'WeArePiccadilly');
        $mail->addReplyTo($email, $name);
        $mail->addAddress('wearepiccadilly@gmail.com'); // ← Destinatario

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = "<b>Nombre:</b> $name<br><b>Correo:</b> $email<br><b>Mensaje:</b><br>$message";
        $mail->AltBody = "Nombre: $name\nCorreo: $email\n\nMensaje:\n$message";

        $mail->send();
        echo json_encode(["message" => "Correo enviado correctamente."]);
    } catch (Exception $e) {
        http_response_code(500); // Error interno
        echo json_encode(["error" => "Error al enviar el correo: {$mail->ErrorInfo}"]);
    }
} else {
    http_response_code(405); // Método no permitido
    echo json_encode(["error" => "Acceso no permitido."]);
}
