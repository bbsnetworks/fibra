<?php
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

$emailCliente = trim($_POST['email_cliente'] ?? '');
$idcontrato   = trim($_POST['idcontrato'] ?? '');
$nombre       = trim($_POST['nombre'] ?? '');

$correoRespaldo = 'contratos@bbsnetworks.net';

// Segundo archivo adjunto fijo
$archivoExtra = __DIR__ . '/../files/cdm.pdf';

if ($emailCliente === '' || !filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {
  echo json_encode([
    'status' => 'error',
    'message' => 'El correo del cliente no es válido.'
  ]);
  exit;
}

if (!isset($_FILES['contrato_pdf']) || $_FILES['contrato_pdf']['error'] !== UPLOAD_ERR_OK) {
  echo json_encode([
    'status' => 'error',
    'message' => 'No se recibió el PDF del contrato.'
  ]);
  exit;
}

try {
  $mail = new PHPMailer(true);

  $mail->isSMTP();
  $mail->Host       = 'smtp.titan.email';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'noreply@bbsnetworks.net';
  $mail->Password   = 'Admin1_Pinck';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;

  $mail->CharSet = 'UTF-8';

  $mail->setFrom('noreply@bbsnetworks.net', 'BBS Networks');
  $mail->addAddress($emailCliente, $nombre);
  $mail->addAddress($correoRespaldo, 'Respaldo contratos');

  $mail->isHTML(true);
  $mail->Subject = "Contrato BBS Networks #{$idcontrato}";

  $mail->Body = "
    <h2>Contrato BBS Networks</h2>
    <p>Hola {$nombre}, adjuntamos tu contrato de servicio.</p>
    <p>También se adjunta documentación adicional relacionada con tu servicio.</p>
    <p>Gracias por confiar en BBS Networks.</p>
  ";

  $mail->AltBody = "Hola {$nombre}, adjuntamos tu contrato de servicio BBS Networks.";

  $mail->addAttachment(
    $_FILES['contrato_pdf']['tmp_name'],
    "contrato_{$idcontrato}.pdf"
  );

  if (file_exists($archivoExtra)) {
    $mail->addAttachment($archivoExtra, 'documento_adicional.pdf');
  }

  $mail->send();

  echo json_encode([
    'status' => 'success',
    'message' => 'Correo enviado correctamente.'
  ]);
} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Error al enviar correo: ' . $mail->ErrorInfo
  ]);
}