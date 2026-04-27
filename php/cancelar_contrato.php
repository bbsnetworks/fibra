<?php
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');
session_start();

/**
 * Normaliza nombres para comparar (quita acentos si iconv está disponible)
 */
function normName($s) {
  $s = trim((string)$s);
  $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
  $s = strtolower($s);
  $s = preg_replace('/\s+/', ' ', $s);
  return $s;
}

$inTx = false;

try {
  if (!($conexion instanceof mysqli)) {
    throw new Exception("No hay conexión mysqli válida.");
  }
  if ($conexion->connect_error) {
    throw new Exception('Conexión fallida: ' . $conexion->connect_error);
  }

  $input   = json_decode(file_get_contents("php://input"), true) ?? [];
  $id      = isset($input['id']) ? (int)$input['id'] : 0;
  $equipos = trim($input['equipos'] ?? "");
  $user    = $_SESSION['username'] ?? 'sistema';
  $force   = !empty($input['force']) ? 1 : 0;

  if ($id <= 0)        throw new Exception('ID de contrato inválido');
  if ($equipos === "") throw new Exception('Debes ingresar los equipos devueltos');

  // ============================
  // TRANSACTION
  // ============================
  $conexion->begin_transaction();
  $inTx = true;

  // 1) Intentar leer contrato
  $q = $conexion->prepare("
    SELECT idcontrato, status, folio_cancelacion, fecha_cancelacion, cancelado_por, nombre, es_legado
    FROM contratos
    WHERE idcontrato=?
    FOR UPDATE
  ");
  if (!$q) throw new Exception("Prepare error (select contrato): " . $conexion->error);

  $q->bind_param("i", $id);
  $q->execute();
  $row = $q->get_result()->fetch_assoc();
  $q->close();

  // 1.1) Si NO existe contrato: crear contrato LEGADO desde clientes
  if (!$row) {
    $qc = $conexion->prepare("
      SELECT idcliente, nombre, direccion, localidad, estado, telefono, paquete, mensualidad
      FROM clientes
      WHERE idcliente=?
      LIMIT 1
    ");
    if (!$qc) throw new Exception("Prepare error (select cliente): " . $conexion->error);

    $qc->bind_param("i", $id);
    $qc->execute();
    $cli = $qc->get_result()->fetch_assoc();
    $qc->close();

    if (!$cli) {
      throw new Exception("Cliente no encontrado en clientes (id={$id}).");
    }

    // Datos base desde clientes
    $fecha      = date('Y-m-d');
    $fechai     = $fecha;
    $hora       = date('H:i:s');

    $nombre     = (string)($cli['nombre'] ?? '');
    $calle      = (string)($cli['direccion'] ?? '');   // dirección completa
    $municipio  = (string)($cli['localidad'] ?? '');
    $estado     = (string)($cli['estado'] ?? '');
    $telefono   = (string)($cli['telefono'] ?? '');
    $tarifa     = (string)($cli['paquete'] ?? '');
    $tmens      = (double)($cli['mensualidad'] ?? 0);

    // --- Insert LEGADO (solo 13 binds, el resto va fijo en SQL) ---
$fecha  = date('Y-m-d');
$fechai = $fecha;
$hora   = date('H:i:s');

$nombre     = (string)($cli['nombre'] ?? '');
$calle      = (string)($cli['direccion'] ?? '');
$municipio  = (string)($cli['localidad'] ?? '');
$estado     = (string)($cli['estado'] ?? '');   // <- si en clientes no existe "estado", cámbialo por el campo real
$telefono   = (string)($cli['telefono'] ?? '');
$tarifa     = (string)($cli['paquete'] ?? '');
$tmens      = (double)($cli['mensualidad'] ?? 0);

$domicilioi = $calle;
$cciudad    = $municipio;

$ins = $conexion->prepare("
  INSERT INTO contratos (
    idcontrato, nombre, rlegal,
    calle, numero, colonia, municipio, cp, estado,
    equipos_devueltos,
    telefono, ttelefono, rfc,
    fecha, tarifa, tmensualidad,
    reconexion, mdesconexion, plazo,
    modeme, marca, modelo, nserie, nequipo,
    pagoum, pequipo,
    domicilioi,
    fechai, hora, costoi,
    autorizacion, mpago, vigencia,
    banco, notarjeta,
    sadicional1, dadicional1, costoa1,
    sadicional2, dadicional2, costoa2,
    sfacturable1, dfacturable1, costof1,
    sfacturable2, dfacturable2, costof2,
    ccontrato, cderechos, cciudad,
    firma1, firma2, evidencia,
    es_legado, status
  ) VALUES (
    ?, ?, '',
    ?, '', '', ?, '', ?,
    NULL,
    ?, 'movil', NULL,
    ?, ?, ?,
    '1', NULL, 0,
    '1', '', '', '', 0,
    '0', 0,
    ?,
    ?, ?, NULL,
    'no', '0', 0,
    NULL, NULL,
    NULL, NULL, NULL,
    NULL, NULL, NULL,
    NULL, NULL, NULL,
    NULL, NULL, NULL,
    0, 0, ?,
    '', '', '',
    1, 'activo'
  )
");

if (!$ins) throw new Exception("Prepare error (insert legado): " . $conexion->error);

// ✅ 13 variables => ✅ 13 tipos
$ins->bind_param(
  "isssssssdssss",
  $id, $nombre,
  $calle, $municipio, $estado, $telefono,
  $fecha, $tarifa, $tmens,
  $domicilioi, $fechai, $hora, $cciudad
);

if (!$ins->execute()) {
  throw new Exception("Error al crear contrato legado: " . $ins->error);
}
$ins->close();

    // Releer contrato creado
    $q2 = $conexion->prepare("
      SELECT idcontrato, status, folio_cancelacion, fecha_cancelacion, cancelado_por, nombre, es_legado
      FROM contratos
      WHERE idcontrato=?
      FOR UPDATE
    ");
    if (!$q2) throw new Exception("Prepare error (reselect contrato): " . $conexion->error);
    $q2->bind_param("i", $id);
    $q2->execute();
    $row = $q2->get_result()->fetch_assoc();
    $q2->close();

    if (!$row) {
      throw new Exception("No se pudo crear el contrato legado.");
    }
  }

  // 1.2) Validación de conflicto (si existe cliente con ese ID y nombre difiere)
  $qc2 = $conexion->prepare("SELECT nombre FROM clientes WHERE idcliente=? LIMIT 1");
  if (!$qc2) throw new Exception("Prepare error (conflicto cliente): " . $conexion->error);

  $qc2->bind_param("i", $id);
  $qc2->execute();
  $cli2 = $qc2->get_result()->fetch_assoc();
  $qc2->close();

  if ($cli2) {
    $nCli = normName($cli2['nombre'] ?? '');
    $nCon = normName($row['nombre'] ?? '');

    if ($nCli !== '' && $nCon !== '' && $nCli !== $nCon && !$force) {
      $conexion->rollback();
      $inTx = false;

      echo json_encode([
        'ok' => false,
        'conflict' => true,
        'message' => 'Conflicto de nombres. Confirmación requerida.',
        'clientes_nombre' => $cli2['nombre'],
        'contratos_nombre' => $row['nombre'],
        'id' => (string)$id
      ]);
      exit;
    }
  }

  // 2) Folio: si ya tenía, respetar; si no, generar
  $yaTieneFolio = !empty($row['folio_cancelacion']);
  $folioBonito  = $row['folio_cancelacion'] ?? null;

  if (!$yaTieneFolio) {
    $sqlSeq = "
      INSERT INTO folio_cancelacion_seq (fecha, consecutivo)
      VALUES (CURDATE(), 1)
      ON DUPLICATE KEY UPDATE consecutivo = LAST_INSERT_ID(consecutivo + 1)
    ";
    if (!$conexion->query($sqlSeq)) {
      throw new Exception('Error generando folio: ' . $conexion->error);
    }
    $n = (int)$conexion->insert_id;
    $folioBonito = sprintf('CNL-%s-%06d', date('Ymd'), $n);
  }

  // 3) Cancelación (sin pisar fecha/cancelado_por/folio si ya existían)
  $now = date('Y-m-d H:i:s');
  $upd = $conexion->prepare("
    UPDATE contratos
    SET status='cancelado',
        equipos_devueltos=?,
        fecha_cancelacion = COALESCE(fecha_cancelacion, ?),
        cancelado_por     = COALESCE(cancelado_por, ?),
        folio_cancelacion = COALESCE(folio_cancelacion, ?)
    WHERE idcontrato=?
  ");
  if (!$upd) throw new Exception("Prepare error (update cancelación): " . $conexion->error);

  $upd->bind_param("ssssi", $equipos, $now, $user, $folioBonito, $id);

  if (!$upd->execute()) {
    throw new Exception('Error al cancelar: ' . $upd->error);
  }
  $upd->close();

  // 4) Datos para PDF
  $sel = $conexion->prepare("
    SELECT idcontrato, status, fecha_cancelacion, equipos_devueltos, cancelado_por,
           folio_cancelacion, nombre, rfc, telefono, modeme, marca, modelo, nserie, nequipo,
           es_legado,
           CASE
             WHEN es_legado = 1 THEN calle
             ELSE CONCAT(calle,' ',numero,', ',colonia,', ',municipio,', ',estado,' C.P. ',cp)
           END AS direccion
    FROM contratos
    WHERE idcontrato=?
    LIMIT 1
  ");
  if (!$sel) throw new Exception("Prepare error (select pdf): " . $conexion->error);

  $sel->bind_param("i", $id);
  $sel->execute();
  $res = $sel->get_result()->fetch_assoc();
  $sel->close();

  $conexion->commit();
  $inTx = false;

  echo json_encode([
    'ok' => true,
    'message' => ((int)($res['es_legado'] ?? 0) === 1)
      ? 'Cliente resagado cancelado (contrato legado creado).'
      : 'Contrato cancelado correctamente',
    'data' => [
      'idcontrato'        => (string)$res['idcontrato'],
      'status'            => $res['status'],
      'fecha_cancelacion' => $res['fecha_cancelacion'],
      'equipos_devueltos' => $res['equipos_devueltos'],
      'cancelado_por'     => $res['cancelado_por'] ?: $user,
      'folio_cancelacion' => $res['folio_cancelacion'],
      'nombre'            => $res['nombre'],
      'rfc'               => $res['rfc'],
      'telefono'          => $res['telefono'],
      'direccion'         => $res['direccion'],
      'marca'             => $res['marca'],
      'modelo'            => $res['modelo'],
      'nserie'            => $res['nserie'],
      'nequipo'           => $res['nequipo'],
      'modeme'            => $res['modeme'],
      'es_legado'         => (int)$res['es_legado']
    ]
  ]);

} catch (Exception $e) {
  if ($conexion instanceof mysqli && $inTx) {
    @ $conexion->rollback();
  }
  echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
} finally {
  if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
  }
}