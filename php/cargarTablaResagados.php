<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/conexion.php';

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);

$filtro = $body['filtro'] ?? 'pendientes';

$where = "";
switch ($filtro) {
  case 'pendientes':
    $where = "co.idcontrato IS NULL";
    break;
  case 'legado':
    $where = "co.idcontrato IS NOT NULL AND co.es_legado = 1";
    break;
  case 'cancelados':
    $where = "co.idcontrato IS NOT NULL AND co.es_legado = 1 AND co.status = 'cancelado'";
    break;
  case 'todos':
  default:
    $where = "(co.idcontrato IS NULL OR co.es_legado = 1)";
    break;
}

$sql = "
  SELECT
    c.idcliente,
    c.nombre,
    c.direccion,
    c.localidad,
    c.estado AS estado_cliente,
    c.telefono,
    c.email,
    c.paquete,
    c.mensualidad,
    co.status AS status_contrato,
    co.es_legado,
    co.folio_cancelacion,
    co.fecha_cancelacion
  FROM clientes c
  LEFT JOIN contratos co
    ON co.idcontrato = c.idcliente
  WHERE $where
  ORDER BY c.idcliente DESC
";

$res = $conexion->query($sql);

if (!$res) {
  echo '
    <div class="rounded-2xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
      Error SQL: ' . htmlspecialchars($conexion->error, ENT_QUOTES, 'UTF-8') . '
    </div>';
  exit;
}

if ($res->num_rows === 0) {
  echo '
    <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
      <div>
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
          <i class="bi bi-inbox text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-white">Sin resultados</h3>
        <p class="mt-2 text-sm text-white/55">No se encontraron clientes rezagados con el filtro seleccionado.</p>
      </div>
    </div>';
  exit;
}

echo '<table id="tablaResagados" class="min-w-full text-sm text-white">';
echo '<thead>';
echo '<tr>';
echo '<th>Creado</th>';
echo '<th>ID</th>';
echo '<th>Nombre</th>';
echo '<th>Dirección</th>';
echo '<th>Fecha</th>';
echo '<th>Status</th>';
echo '<th>Detalle</th>';
echo '<th>Comprobante</th>';
echo '<th>Acciones</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

while ($row = $res->fetch_assoc()) {
  $id = (int)$row['idcliente'];

  $nombre = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
  $telefono = htmlspecialchars($row['telefono'] ?? '', ENT_QUOTES, 'UTF-8');
  $direccion = trim(($row['direccion'] ?? '') . ', ' . ($row['localidad'] ?? ''));
  $direccion = htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8');

  $paquete = htmlspecialchars($row['paquete'] ?? '', ENT_QUOTES, 'UTF-8');
  $mensualidad = htmlspecialchars((string)($row['mensualidad'] ?? '0'), ENT_QUOTES, 'UTF-8');

  $status = strtolower(trim($row['status_contrato'] ?? 'sin_contrato'));
  $esLegado = (int)($row['es_legado'] ?? 0) === 1;

  $fechaBase = $row['fecha_cancelacion'] ?? null;
  $fechaMostrar = $fechaBase ? date('Y-m-d H:i', strtotime($fechaBase)) : '—';

  $tipo = $esLegado ? 'LEGADO' : 'PENDIENTE';
  $detalle = $paquete !== '' ? "{$paquete} · $" . $mensualidad : $tipo;

  // Columna creado
  if ($status === 'cancelado') {
    $creadoIcon = '
      <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-500/15 text-red-400" title="Cancelado">
        <i class="bi bi-x-circle-fill text-lg"></i>
      </span>';
  } elseif ($esLegado) {
    $creadoIcon = '
      <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300" title="Legado">
        <i class="bi bi-clock-history text-lg"></i>
      </span>';
  } else {
    $creadoIcon = '
      <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-500/15 text-cyan-300" title="Pendiente">
        <i class="bi bi-hourglass-split text-lg"></i>
      </span>';
  }

  // Badge status
  if ($status === 'activo') {
    $statusHtml = '
      <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
        <i class="bi bi-check-circle-fill"></i> Activo
      </span>';
  } elseif ($status === 'cancelado') {
    $statusHtml = '
      <span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-400">
        <i class="bi bi-x-circle-fill"></i> Cancelado
      </span>';
  } elseif ($status === 'pausado') {
    $statusHtml = '
      <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300">
        <i class="bi bi-pause-circle-fill"></i> Pausado
      </span>';
  } else {
    $statusHtml = '
      <span class="inline-flex items-center gap-2 rounded-full bg-slate-500/15 px-3 py-1 text-xs font-semibold text-slate-300">
        <i class="bi bi-dash-circle-fill"></i> Sin contrato
      </span>';
  }

  $detalleHtml = '
    <div class="text-center">
      <div class="text-slate-100 font-medium">' . htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') . '</div>
      <div class="text-xs text-slate-400 mt-1">' . htmlspecialchars($detalle, ENT_QUOTES, 'UTF-8') . '</div>
    </div>';

  // Comprobante
  if ($status === 'cancelado') {
    $btnComprobante = '
      <button
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/90 text-white shadow-lg shadow-violet-500/20 transition hover:scale-[1.03] hover:bg-violet-400"
        title="Descargar comprobante de cancelación"
        onclick="descargarCancelacion(' . $id . ')">
        <i class="bi bi-file-earmark-arrow-down text-lg"></i>
      </button>';
  } else {
    $btnComprobante = '<span class="text-slate-500">—</span>';
  }

  // Acciones
  $acciones = [];

  if ($status === 'cancelado') {
    $acciones[] = '
      <button
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/90 text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.03] hover:bg-emerald-400"
        onclick="confirmarReactivacion(' . $id . ')"
        title="Reactivar">
        <i class="bi bi-arrow-clockwise text-lg"></i>
      </button>';
  } else {
    $acciones[] = '
      <button
        class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/90 text-white shadow-lg shadow-red-500/20 transition hover:scale-[1.03] hover:bg-red-400"
        onclick="cancelarResagado(' . $id . ')"
        title="Cancelar">
        <i class="bi bi-x-circle text-lg"></i>
      </button>';
  }

  echo '<tr>';
  echo '<td>' . $creadoIcon . '</td>';
  echo '<td>' . $id . '</td>';
  echo '<td>' . $nombre . '<div class="mt-1 text-xs text-slate-400">' . $telefono . '</div></td>';
  echo '<td>' . $direccion . '</td>';
  echo '<td>' . htmlspecialchars($fechaMostrar, ENT_QUOTES, 'UTF-8') . '</td>';
  echo '<td>' . $statusHtml . '</td>';
  echo '<td>' . $detalleHtml . '</td>';
  echo '<td>' . $btnComprobante . '</td>';
  echo '<td>' . implode(' ', $acciones) . '</td>';
  echo '</tr>';
}

echo '</tbody></table>';

$conexion->close();
?>