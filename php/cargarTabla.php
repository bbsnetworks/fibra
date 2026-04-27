<?php
include("conexion.php");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$postdata = json_decode(file_get_contents("php://input"));

$estado = isset($postdata->estado) ? strtolower(trim($postdata->estado)) : 'activo';
$busqueda = isset($postdata->busqueda) ? trim($postdata->busqueda) : '';

// Filtro por estado
$where = [];

if (in_array($estado, ['activo', 'cancelado', 'pausado'])) {
    $estadoSafe = $conexion->real_escape_string($estado);
    $where[] = "status = '$estadoSafe'";
}

// Filtro por búsqueda
if ($busqueda !== '') {
    $busquedaSafe = $conexion->real_escape_string($busqueda);
    $where[] = "(
        idcontrato LIKE '%$busquedaSafe%' OR
        nombre LIKE '%$busquedaSafe%' OR
        calle LIKE '%$busquedaSafe%' OR
        numero LIKE '%$busquedaSafe%' OR
        colonia LIKE '%$busquedaSafe%' OR
        municipio LIKE '%$busquedaSafe%'
    )";
}

$filtro_sql = '';
if (!empty($where)) {
    $filtro_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
SELECT 
  idcontrato,
  nombre,
  CONCAT(calle, ' ', numero, ', ', colonia, ', ', municipio) AS direccion,
  fecha,
  fecha_cancelacion,
  tarifa,
  status
FROM contratos
$filtro_sql
ORDER BY idcontrato DESC
";

$result = $conexion->query($sql);

function paqueteTexto($tarifa)
{
    switch ((string)$tarifa) {
        case "1": return "Residencial 7 MB/s";
        case "2": return "BBS Air 10";
        case "3": return "Residencial 15 MB/s";
        case "4": return "BBS Air 20";
        case "5": return "Residencial 40 MB/s";
        case "6": return "Residencial 50 MB/s";
        case "7": return "BBS Air 30";
        case "8": return "Residencial 80 MB/s";
        default: return (string)$tarifa;
    }
}

if ($result && $result->num_rows > 0) {
    echo '<div class="w-full overflow-x-auto">';
    echo '<table class="min-w-full overflow-hidden rounded-2xl text-sm text-white">';
    
    echo '<thead>';
    echo '<tr class="border-b border-white/10 bg-[#0f172a] text-xs uppercase tracking-wide text-slate-300">';
    echo '<th class="px-4 py-4 text-center font-semibold">Creado</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">ID</th>';
    echo '<th class="px-4 py-4 text-left font-semibold">Nombre</th>';
    echo '<th class="px-4 py-4 text-left font-semibold">Dirección</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">Fecha</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">Status</th>';

    if ($estado === 'cancelado') {
        echo '<th class="px-4 py-4 text-center font-semibold">Fecha cancelación</th>';
    } else {
        echo '<th class="px-4 py-4 text-center font-semibold">Paquete</th>';
    }

    echo '<th class="px-4 py-4 text-center font-semibold">Comprobante</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">Editar</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">Descargar</th>';
    echo '<th class="px-4 py-4 text-center font-semibold">Crear</th>';

    if ($estado === 'activo') {
        echo '<th class="px-4 py-4 text-center font-semibold">Cancelar</th>';
    } elseif ($estado === 'cancelado') {
        echo '<th class="px-4 py-4 text-center font-semibold">Reactivar</th>';
    }

    echo '</tr>';
    echo '</thead>';

    echo '<tbody>';

    while ($row = $result->fetch_assoc()) {
        $idc = (int)$row['idcontrato'];
        $nombre = htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8');
        $dir = htmlspecialchars($row['direccion'] ?? '', ENT_QUOTES, 'UTF-8');
        $fecha = htmlspecialchars($row['fecha'] ?? '', ENT_QUOTES, 'UTF-8');
        $fcanc = htmlspecialchars($row['fecha_cancelacion'] ?? '', ENT_QUOTES, 'UTF-8');
        $status = strtolower(trim($row['status'] ?? ''));
        $tarifa = htmlspecialchars($row['tarifa'] ?? '', ENT_QUOTES, 'UTF-8');

        // Verifica si ya existe usuario creado
        $sql2 = "SELECT CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM contratos c
                        JOIN clientes cl ON c.idcontrato = cl.idcliente
                        WHERE c.idcontrato = $idc
                    ) THEN TRUE
                    ELSE FALSE
                END AS usuario_creado";
        $result2 = $conexion->query($sql2);

        $usuario_creado = 0;
        if ($result2 && $row2 = $result2->fetch_assoc()) {
            $usuario_creado = (int)$row2['usuario_creado'];
        }

        echo '<tr class="border-b border-white/5 bg-white/[0.02] transition hover:bg-cyan-400/5">';

        // Columna creado
        echo '<td class="px-4 py-4 text-center">';
        if ($status === "cancelado") {
            echo '<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-red-500/15 text-red-400" title="Contrato cancelado">
                    <i class="bi bi-x-circle-fill text-lg"></i>
                  </span>';
        } elseif ($usuario_creado === 1) {
            echo '<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400" title="Cliente creado">
                    <i class="bi bi-check-circle-fill text-lg"></i>
                  </span>';
        } else {
            echo '<span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300" title="Cliente no creado">
                    <i class="bi bi-exclamation-circle-fill text-lg"></i>
                  </span>';
        }
        echo '</td>';

        echo '<td class="px-4 py-4 text-center">
                <span class="inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">'
                . $idc .
             '</span>
              </td>';

        echo '<td class="px-4 py-4 text-left font-medium text-white">' . $nombre . '</td>';
        echo '<td class="px-4 py-4 text-left text-slate-300">' . $dir . '</td>';
        echo '<td class="px-4 py-4 text-center text-slate-200 whitespace-nowrap">' . $fecha . '</td>';

        // Status badge
        echo '<td class="px-4 py-4 text-center">';
        if ($status === "activo") {
            echo '<span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                    <i class="bi bi-check-circle-fill"></i> Activo
                  </span>';
        } elseif ($status === "cancelado") {
            echo '<span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-400">
                    <i class="bi bi-x-circle-fill"></i> Cancelado
                  </span>';
        } elseif ($status === "pausado") {
            echo '<span class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300">
                    <i class="bi bi-pause-circle-fill"></i> Pausado
                  </span>';
        } else {
            echo '<span class="text-slate-300">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
        }
        echo '</td>';

        // Paquete o fecha cancelación
        if ($estado === 'cancelado') {
            $fcanc_fmt = $fcanc ? date('Y-m-d H:i', strtotime($fcanc)) : '—';
            echo '<td class="px-4 py-4 text-center text-slate-200 whitespace-nowrap">' . $fcanc_fmt . '</td>';
        } else {
            echo '<td class="px-4 py-4 text-center text-slate-100">' . paqueteTexto($tarifa) . '</td>';
        }

        // Comprobante
        echo '<td class="px-4 py-4 text-center">';
        if ($status === 'cancelado') {
            echo '<button
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/90 text-white shadow-lg shadow-violet-500/20 transition hover:scale-[1.03] hover:bg-violet-400"
                    title="Descargar comprobante de cancelación"
                    onclick="descargarCancelacion(' . $idc . ')">
                    <i class="bi bi-file-earmark-arrow-down text-lg"></i>
                  </button>';
        } else {
            echo '<span class="text-slate-500">—</span>';
        }
        echo '</td>';

        // Editar
        echo '<td class="px-4 py-4 text-center">
                <button
                  class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/90 text-white shadow-lg shadow-amber-500/20 transition hover:scale-[1.03] hover:bg-amber-400"
                  onclick="editContract(' . $idc . ')"
                  title="Editar contrato">
                  <i class="bi bi-pencil-square text-lg"></i>
                </button>
              </td>';

        // Descargar
        echo '<td class="px-4 py-4 text-center">
                <button
                  class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/90 text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.03] hover:bg-emerald-400"
                  onclick="descargarContrato(' . $idc . ')"
                  title="Descargar contrato">
                  <i class="bi bi-download text-lg"></i>
                </button>
              </td>';

        // Crear
        echo '<td class="px-4 py-4 text-center">
                <button
                  class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/90 text-white shadow-lg shadow-cyan-500/20 transition hover:scale-[1.03] hover:bg-cyan-400"
                  onclick="addContract(' . $idc . ')"
                  title="Crear cliente">
                  <i class="bi bi-person-plus text-lg"></i>
                </button>
              </td>';

        // Acción dinámica
        if ($estado === 'activo') {
            echo '<td class="px-4 py-4 text-center">
                    <button
                      class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/90 text-white shadow-lg shadow-red-500/20 transition hover:scale-[1.03] hover:bg-red-400"
                      onclick="confirmarCancelacion(' . $idc . ')"
                      title="Cancelar contrato">
                      <i class="bi bi-x-circle text-lg"></i>
                    </button>
                  </td>';
        } elseif ($estado === 'cancelado') {
            echo '<td class="px-4 py-4 text-center">
                    <button
                      class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/90 text-white shadow-lg shadow-emerald-500/20 transition hover:scale-[1.03] hover:bg-emerald-400"
                      onclick="confirmarReactivacion(' . $idc . ')"
                      title="Reactivar contrato">
                      <i class="bi bi-arrow-clockwise text-lg"></i>
                    </button>
                  </td>';
        }

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} else {
    echo '
    <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
      <div>
        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
          <i class="bi bi-inbox text-xl"></i>
        </div>
        <h3 class="text-base font-semibold text-white">Sin resultados</h3>
        <p class="mt-2 text-sm text-white/55">No se encontraron contratos con los filtros seleccionados.</p>
      </div>
    </div>';
}

$conexion->close();
?>