<?php
include("conexion.php");

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}

$postdata = json_decode(file_get_contents("php://input"));

$estado = isset($postdata->estado) ? strtolower(trim($postdata->estado)) : 'activo';
$busqueda = isset($postdata->busqueda) ? trim($postdata->busqueda) : '';

$where = [];

if (in_array($estado, ['activo', 'cancelado', 'pausado'])) {
    $estadoSafe = $conexion->real_escape_string($estado);
    $where[] = "c.status = '$estadoSafe'";
}

if ($busqueda !== '') {
    $busquedaSafe = $conexion->real_escape_string($busqueda);
    $where[] = "(
        c.idcontrato LIKE '%$busquedaSafe%' OR
        c.nombre LIKE '%$busquedaSafe%' OR
        c.calle LIKE '%$busquedaSafe%' OR
        c.numero LIKE '%$busquedaSafe%' OR
        c.colonia LIKE '%$busquedaSafe%' OR
        c.municipio LIKE '%$busquedaSafe%'
    )";
}

$filtro_sql = '';
if (!empty($where)) {
    $filtro_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "
SELECT 
    c.idcontrato,
    c.nombre,
    CONCAT(c.calle, ' ', c.numero, ', ', c.colonia, ', ', c.municipio) AS direccion,
    c.fecha,
    c.fecha_cancelacion,
    c.tarifa,
    c.status,
    c.ubicacion_lat,
    c.ubicacion_lng,
    c.ubicacion_precision,
    c.ubicacion_fuente,
    c.ubicacion_fecha,
    CASE 
        WHEN cl.idcliente IS NOT NULL THEN 1
        ELSE 0
    END AS usuario_creado
FROM contratos c
LEFT JOIN clientes cl ON c.idcontrato = cl.idcliente
$filtro_sql
ORDER BY c.idcontrato DESC
";

$result = $conexion->query($sql);

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function paqueteTexto($tarifa): string
{
    switch ((string) $tarifa) {
        case "1":
            return "Residencial 7 MB/s";
        case "2":
            return "BBS Air 10";
        case "3":
            return "Residencial 15 MB/s";
        case "4":
            return "BBS Air 20";
        case "5":
            return "Residencial 40 MB/s";
        case "6":
            return "Residencial 50 MB/s";
        case "7":
            return "BBS Air 30";
        case "8":
            return "BBS Fiber 30";
        default:
            return (string) $tarifa;
    }
}

function statusBadge($status): string
{
    $status = strtolower(trim((string)$status));

    if ($status === "activo") {
        return '
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                <i class="bi bi-check-circle-fill"></i>
                Activo
            </span>';
    }

    if ($status === "cancelado") {
        return '
            <span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-400">
                <i class="bi bi-x-circle-fill"></i>
                Cancelado
            </span>';
    }

    if ($status === "pausado") {
        return '
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300">
                <i class="bi bi-pause-circle-fill"></i>
                Pausado
            </span>';
    }

    return '
        <span class="inline-flex items-center gap-2 rounded-full bg-slate-500/15 px-3 py-1 text-xs font-semibold text-slate-300">
            ' . h($status) . '
        </span>';
}

function estadoCreadoBadge($status, $usuarioCreado): string
{
    $status = strtolower(trim((string)$status));

    if ($status === "cancelado") {
        return '
            <span class="inline-flex items-center gap-2 rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-400">
                <i class="bi bi-x-circle-fill"></i>
                Cancelado
            </span>';
    }

    if ((int)$usuarioCreado === 1) {
        return '
            <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-400">
                <i class="bi bi-check-circle-fill"></i>
                Cliente creado
            </span>';
    }

    return '
        <span class="inline-flex items-center gap-2 rounded-full bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-300">
            <i class="bi bi-exclamation-circle-fill"></i>
            Pendiente de crear
        </span>';
}

function botonAccion($icono, $titulo, $clases, $onclick): string
{
    return '
        <button
            type="button"
            class="min-h-[46px] w-full inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold text-white shadow-lg transition duration-200 hover:-translate-y-0.5 hover:scale-[1.01] focus:outline-none focus:ring-2 focus:ring-cyan-300/50 ' . $clases . '"
            onclick="' . $onclick . '"
            title="' . h($titulo) . '">
            <i class="bi ' . h($icono) . ' text-lg"></i>
            <span>' . h($titulo) . '</span>
        </button>
    ';
}

if ($result && $result->num_rows > 0) {
    echo '<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">';

    while ($row = $result->fetch_assoc()) {
        $idc = (int) $row['idcontrato'];
        $nombre = h($row['nombre']);
        $nombreData = h($row['nombre']);
        $dir = h($row['direccion']);
        $fecha = h($row['fecha']);
        $status = strtolower(trim((string)($row['status'] ?? '')));
        $paquete = h(paqueteTexto($row['tarifa']));
        $usuarioCreado = (int)($row['usuario_creado'] ?? 0);

        $fechaCancelacion = $row['fecha_cancelacion']
            ? date('Y-m-d H:i', strtotime($row['fecha_cancelacion']))
            : '—';

        $lat = $row['ubicacion_lat'];
        $lng = $row['ubicacion_lng'];
        $precision = $row['ubicacion_precision'];
        $fuente = $row['ubicacion_fuente'];

        $fechaUbicacion = $row['ubicacion_fecha']
            ? date('Y-m-d H:i', strtotime($row['ubicacion_fecha']))
            : '';

        $tieneUbicacion = ($lat !== null && $lat !== '' && $lng !== null && $lng !== '');

        echo '
        <article class="group relative overflow-hidden rounded-3xl border border-white/10 bg-[#0b1a2d] p-5 shadow-xl shadow-black/20 transition duration-200 hover:border-cyan-400/30 hover:bg-[#0d2038]">
            
            <div class="absolute right-0 top-0 h-32 w-32 rounded-bl-full bg-cyan-400/5 transition group-hover:bg-cyan-400/10"></div>

            <div class="relative flex flex-col gap-5">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-bold text-cyan-300">
                                Contrato #' . $idc . '
                            </span>
                            ' . statusBadge($status) . '
                        </div>

                        <h3 class="truncate text-lg font-bold text-white">
                            ' . $nombre . '
                        </h3>

                        <p class="mt-2 flex items-start gap-2 text-sm text-slate-300">
                            <i class="bi bi-geo-alt text-cyan-300 mt-0.5"></i>
                            <span>' . $dir . '</span>
                        </p>
                    </div>

                    <div class="shrink-0">
                        ' . estadoCreadoBadge($status, $usuarioCreado) . '
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-[#071322] p-4">
                        <p class="mb-1 text-xs uppercase tracking-wide text-white/40">Fecha</p>
                        <p class="mb-0 text-sm font-semibold text-white">' . ($fecha ?: '—') . '</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#071322] p-4">
                        <p class="mb-1 text-xs uppercase tracking-wide text-white/40">' . ($status === 'cancelado' ? 'Cancelación' : 'Paquete') . '</p>
                        <p class="mb-0 text-sm font-semibold text-white">' . ($status === 'cancelado' ? h($fechaCancelacion) : $paquete) . '</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#071322] p-4">
                        <p class="mb-1 text-xs uppercase tracking-wide text-white/40">Ubicación</p>';

        if ($tieneUbicacion) {
            echo '
                        <p class="mb-0 text-sm font-semibold text-emerald-400">
                            <i class="bi bi-check-circle-fill"></i>
                            Guardada
                        </p>';
        } else {
            echo '
                        <p class="mb-0 text-sm font-semibold text-slate-500">
                            <i class="bi bi-dash-circle"></i>
                            Sin ubicación
                        </p>';
        }

        echo '
                    </div>
                </div>

                <div class="mt-1 border-t border-white/10 pt-4">
                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        <i class="bi bi-lightning-charge-fill text-cyan-300"></i>
                        Acciones del contrato
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">';

        echo botonAccion(
            'bi-pencil-square',
            'Editar',
            'bg-amber-500/90 shadow-amber-500/20 hover:bg-amber-400',
            'editContract(' . $idc . ')'
        );

        echo botonAccion(
            'bi-download',
            'Descargar',
            'bg-emerald-500/90 shadow-emerald-500/20 hover:bg-emerald-400',
            'descargarContrato(' . $idc . ')'
        );

        echo botonAccion(
            'bi-person-plus',
            'Crear',
            'bg-cyan-500/90 shadow-cyan-500/20 hover:bg-cyan-400',
            'addContract(' . $idc . ')'
        );

        echo botonAccion(
            'bi-envelope-arrow-up',
            'Reenviar',
            'bg-indigo-500/90 shadow-indigo-500/20 hover:bg-indigo-400',
            'abrirModalReenviarContrato(' . $idc . ')'
        );

        if ($tieneUbicacion) {
            echo '
                <button
                    type="button"
                    class="btn-ver-ubicacion-contrato min-h-[46px] w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500/90 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/20 transition duration-200 hover:-translate-y-0.5 hover:scale-[1.01] hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-300/50"
                    data-id="' . $idc . '"
                    data-nombre="' . $nombreData . '"
                    data-lat="' . h($lat) . '"
                    data-lng="' . h($lng) . '"
                    data-precision="' . h($precision) . '"
                    data-fuente="' . h($fuente) . '"
                    data-fecha="' . h($fechaUbicacion) . '"
                    title="Ver ubicación">
                    <i class="bi bi-geo-alt-fill text-lg"></i>
                    <span>Ubicación</span>
                </button>';
        } else {
            echo '
                <button
                    type="button"
                    disabled
                    class="min-h-[46px] w-full inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-2xl bg-slate-700/60 px-4 py-3 text-sm font-bold text-slate-400"
                    title="Este contrato no tiene ubicación guardada">
                    <i class="bi bi-geo-alt text-lg"></i>
                    <span>Sin ubicación</span>
                </button>';
        }

        if ($status === 'cancelado') {
            echo botonAccion(
                'bi-file-earmark-arrow-down',
                'Comprobante',
                'bg-violet-500/90 shadow-violet-500/20 hover:bg-violet-400',
                'descargarCancelacion(' . $idc . ')'
            );

            echo botonAccion(
                'bi-arrow-clockwise',
                'Reactivar',
                'bg-emerald-500/90 shadow-emerald-500/20 hover:bg-emerald-400',
                'confirmarReactivacion(' . $idc . ')'
            );
        } elseif ($status === 'activo') {
            echo botonAccion(
                'bi-x-circle',
                'Cancelar',
                'bg-red-500/90 shadow-red-500/20 hover:bg-red-400',
                'confirmarCancelacion(' . $idc . ')'
            );
        }

        echo '
                    </div>
                </div>
            </div>
        </article>';
    }

    echo '</div>';
} else {
    echo '
    <div class="flex min-h-[260px] items-center justify-center rounded-2xl border border-dashed border-white/10 bg-white/[0.02] p-6 text-center">
        <div>
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-cyan-300">
                <i class="bi bi-inbox text-xl"></i>
            </div>

            <h3 class="text-base font-semibold text-white">
                Sin resultados
            </h3>

            <p class="mt-2 text-sm text-white/55">
                No se encontraron contratos con los filtros seleccionados.
            </p>
        </div>
    </div>';
}

$conexion->close();
?>