<?php
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// =====================
// DATOS RECIBIDOS
// =====================
$emailCliente = trim($_POST['email_cliente'] ?? '');
$idcontrato   = trim($_POST['idcontrato'] ?? '');
$nombre       = trim($_POST['nombre'] ?? '');

// =====================
// CONFIGURACIÓN
// =====================
$correoRespaldo = 'contratos@bbsnetworks.net';

$archivoExtra   = __DIR__ . '/../files/cdm.pdf';
$imagenMetodos  = __DIR__ . '/../files/pagos.jpg';
$logo = __DIR__ . '/../files/logo.png';

// =====================
// VALIDACIONES
// =====================
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

// =====================
// ENVÍO DE CORREO
// =====================
try {
  $mail = new PHPMailer(true);

  // SMTP
  $mail->isSMTP();
  $mail->Host       = 'smtp.titan.email';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'noreply@bbsnetworks.net';
  $mail->Password   = 'Admin1_Pinck';
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  $mail->Port       = 465;

  $mail->CharSet = 'UTF-8';

  // Remitente
  $mail->setFrom('noreply@bbsnetworks.net', 'BBS Networks');
  $mail->addReplyTo('contratos@bbsnetworks.net', 'Contratos BBS Networks');

  // Destinatarios
  $mail->addAddress($emailCliente, $nombre);
  $mail->addAddress($correoRespaldo, 'Respaldo contratos');

  $mail->isHTML(true);
  $mail->Subject = "Contrato BBS Networks #{$idcontrato}";

  // =====================
  // IMAGEN EMBEBIDA
  // =====================
  $cidPagos = 'metodos_pago_bbs';

  $cidLogo = 'logo_bbs';

if (file_exists($logo)) {
  $mail->addEmbeddedImage($logo, $cidLogo, 'logo.png');
}

  if (file_exists($imagenMetodos)) {
    $mail->addEmbeddedImage($imagenMetodos, $cidPagos, 'metodos_pago.jpg');
  }

  // Sanitizar
  $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
  $idSeguro     = htmlspecialchars($idcontrato, ENT_QUOTES, 'UTF-8');

  // =====================
  // BODY HTML PRO
  // =====================
  $mail->Body = "
<!DOCTYPE html>
<html>
<body style='margin:0;background:#eef6ff;font-family:Arial;'>

<table width='100%' style='padding:30px 0;'>
<tr><td align='center'>

<table width='620' style='background:#fff;border-radius:24px;overflow:hidden;'>

<tr>
<td style='background:linear-gradient(135deg,#7b22d8,#80c9ff);padding:40px;text-align:center;'>

<div style='background:#fff;border-radius:20px;padding:14px 30px;display:inline-block;'>
  <img src='cid:logo_bbs' style='height:60px; display:block;'>
</div>

<h2 style='color:#fff;'>Tu contrato ha sido generado</h2>
<p style='color:#fff;'>Hola <b>{$nombreSeguro}</b>, adjuntamos tu contrato.</p>

</td>
</tr>

<tr>
<td style='padding:30px;color:#1b2540;'>

<p>Incluye:</p>
<ul>
<li>Contrato PDF</li>
<li>Carta de derechos</li>
<li>Métodos de pago</li>
</ul>

<div style='text-align:center;margin:20px 0;'>
<b style='color:#5b2de1;font-size:22px;'>Contrato #{$idSeguro}</b>
</div>

<h3 style='text-align:center;color:#5b2de1;'>Métodos de pago</h3>

<img src='cid:{$cidPagos}' style='width:100%;border-radius:15px;'>

<p style='margin-top:20px;font-size:14px;color:#555;'>
Importante: coloca tu número de referencia en el concepto.
</p>

<p>Saludos,<br><b>BBS Networks</b></p>

</td>
</tr>

<tr>
<td style='background:#0b1a2d;color:#fff;text-align:center;padding:15px;font-size:12px;'>
Correo automático · BBS Networks
</td>
</tr>

</table>

</td></tr>
</table>

</body>
</html>
";

  // Versión texto
  $mail->AltBody = "Hola {$nombre}, tu contrato #{$idcontrato} ha sido generado.";

  // =====================
  // ADJUNTOS
  // =====================
  $mail->addAttachment(
    $_FILES['contrato_pdf']['tmp_name'],
    "contrato_{$idcontrato}.pdf"
  );

  if (file_exists($archivoExtra)) {
    $mail->addAttachment($archivoExtra, 'carta_derechos.pdf');
  }

  // Enviar
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