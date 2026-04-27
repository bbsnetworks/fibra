<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once '../php/conexion.php';

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) { echo json_encode(['ok'=>false,'message'=>'ID inválido']); exit; }

$sql = "SELECT
  idcontrato, status, fecha_cancelacion, equipos_devueltos,
  cancelado_por, folio_cancelacion, nombre, rfc, telefono,
  modeme, marca, modelo, nserie, nequipo,
  CONCAT(calle,' ',numero,', ',colonia,', ',municipio,', ',estado,' C.P. ',cp) AS direccion
FROM contratos
WHERE idcontrato = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) { echo json_encode(['ok'=>false,'message'=>'Contrato no encontrado']); exit; }
if (strtolower($res['status']) !== 'cancelado') {
  echo json_encode(['ok'=>false,'message'=>'El contrato no está cancelado']); exit;
}

// Fallbacks por si algo viniera NULL
$cancelado_por = $res['cancelado_por'] ?: ($_SESSION['username'] ?? 'sistema');
$folio = $res['folio_cancelacion'] ?: ('CNL-' . date('Ymd') . '-' . str_pad($res['idcontrato'], 5, '0', STR_PAD_LEFT));

echo json_encode([
  'ok' => true,
  'data' => [
    'idcontrato'        => (string)$res['idcontrato'],
    'folio_cancelacion' => $folio,
    'status'            => $res['status'],
    'fecha_cancelacion' => $res['fecha_cancelacion'],
    'equipos_devueltos' => $res['equipos_devueltos'],
    'cancelado_por'     => $cancelado_por,
    'nombre'            => $res['nombre'],
    'rfc'               => $res['rfc'],
    'telefono'          => $res['telefono'],
    'direccion'         => $res['direccion'],
    'marca'             => $res['marca'],
    'modelo'            => $res['modelo'],
    'nserie'            => $res['nserie'],
    'nequipo'           => $res['nequipo'],
    'modeme'           => $res['modeme']
  ]
]);

