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


/* =====================================================
   LISTADO COMPACTO DE CONTRATOS
===================================================== */

if ($result && $result->num_rows > 0) {

    echo '
    <div class="w-full overflow-x-auto">

        <table class="w-full min-w-[1050px] border-collapse text-sm text-white">

            <thead>
                <tr class="border-b border-white/15 bg-[#10243a] text-left text-xs uppercase tracking-wide text-slate-400">

                    <th class="px-4 py-4">Contrato</th>
                    <th class="px-4 py-4">Cliente</th>
                    <th class="px-4 py-4">Dirección</th>
                    <th class="px-4 py-4">Fecha</th>
                    <th class="px-4 py-4">Paquete</th>
                    <th class="px-4 py-4">Ubicación</th>
                    <th class="px-4 py-4">Estado</th>
                    <th class="px-4 py-4 text-center">Acciones</th>

                </tr>
            </thead>

            <tbody class="divide-y divide-white/10">
    ';

    while ($row = $result->fetch_assoc()) {

        /* ============================================
           DATOS DEL CONTRATO
        ============================================ */

        $idc = (int)$row['idcontrato'];

        $nombre = h($row['nombre']);
        $nombreData = h($row['nombre']);

        $dir = h($row['direccion']);
        $fecha = h($row['fecha']);

        $status = strtolower(
            trim((string)($row['status'] ?? ''))
        );

        $paquete = h(paqueteTexto($row['tarifa']));

        $usuarioCreado = (int)(
            $row['usuario_creado'] ?? 0
        );

        $fechaCancelacion = !empty($row['fecha_cancelacion'])
            ? date(
                'Y-m-d H:i',
                strtotime($row['fecha_cancelacion'])
            )
            : '—';

        /* ============================================
           UBICACIÓN GUARDADA
        ============================================ */

        $lat = $row['ubicacion_lat'];
        $lng = $row['ubicacion_lng'];

        $precision = $row['ubicacion_precision'];
        $fuente = $row['ubicacion_fuente'];

        $fechaUbicacion = !empty($row['ubicacion_fecha'])
            ? date(
                'Y-m-d H:i',
                strtotime($row['ubicacion_fecha'])
            )
            : '';

        $tieneUbicacion = (
            $lat !== null &&
            $lat !== '' &&
            $lng !== null &&
            $lng !== ''
        );

        /* ============================================
           FILA PRINCIPAL
        ============================================ */

        echo '
        <tr class="bg-[#0b1d31] transition-colors hover:bg-[#102b44]">

            <!-- ID CONTRATO -->
            <td class="px-4 py-4">

                <span class="inline-flex rounded-lg bg-cyan-400/10 px-3 py-1.5 font-bold text-cyan-300">
                    #' . $idc . '
                </span>

            </td>

            <!-- NOMBRE -->
            <td class="px-4 py-4">

                <div class="font-semibold text-white">
                    ' . $nombre . '
                </div>

                <div class="mt-1">
                    ' . estadoCreadoBadge(
                        $status,
                        $usuarioCreado
                    ) . '
                </div>

            </td>

            <!-- DIRECCIÓN -->
            <td class="px-4 py-4">

                <div class="max-w-[270px] text-slate-300">

                    <i class="bi bi-geo-alt mr-1 text-cyan-300"></i>

                    ' . $dir . '

                </div>

            </td>

            <!-- FECHA -->
            <td class="whitespace-nowrap px-4 py-4 text-slate-300">
                ' . ($fecha ?: '—') . '
            </td>

            <!-- PAQUETE -->
            <td class="px-4 py-4 font-medium text-white">
                ' . $paquete . '
            </td>

            <!-- UBICACIÓN -->
            <td class="px-4 py-4">
        ';

        if ($tieneUbicacion) {

            echo '
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-400">
                    <i class="bi bi-check-circle-fill"></i>
                    Guardada
                </span>
            ';

        } else {

            echo '
                <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500">
                    <i class="bi bi-dash-circle"></i>
                    Sin ubicación
                </span>
            ';
        }

        echo '
            </td>

            <!-- ESTADO -->
            <td class="px-4 py-4">
                ' . statusBadge($status) . '
            </td>

            <!-- BOTÓN DESPLEGABLE -->
            <td class="px-4 py-4 text-center">

                <button
                    type="button"
                    data-toggle-acciones="' . $idc . '"
                    aria-expanded="false"
                    aria-controls="acciones-' . $idc . '"

                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-400/20 bg-cyan-400/10 text-cyan-300 transition hover:bg-cyan-400/20"

                    title="Mostrar acciones"
                    aria-label="Mostrar acciones del contrato ' . $idc . '"
                >

                    <i class="bi bi-chevron-down icono-acciones text-lg"></i>

                </button>

            </td>

        </tr>
        ';

        /* ============================================
           FILA DE ACCIONES OCULTA
        ============================================ */

        echo '
        <tr
            id="acciones-' . $idc . '"
            class="hidden bg-[#10283f]"
        >

            <td colspan="8" class="px-5 py-5">

                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

                    <div>

                        <h4 class="mb-1 text-sm font-bold text-cyan-300">
                            <i class="bi bi-lightning-charge-fill mr-1"></i>
                            Acciones del contrato #' . $idc . '
                        </h4>

                        <p class="mb-0 text-xs text-slate-400">
                            Selecciona la operación que deseas realizar.
                        </p>

                    </div>
        ';

        if ($status === 'cancelado') {

            echo '
                <div class="text-xs text-red-300">
                    <i class="bi bi-calendar-x mr-1"></i>
                    Cancelado: ' . h($fechaCancelacion) . '
                </div>
            ';
        }

        echo '
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        ';

        /* ============================================
           EDITAR
        ============================================ */

        echo botonAccion(
            'bi-pencil-square',
            'Editar',
            'bg-amber-500/90 shadow-amber-500/20 hover:bg-amber-400',
            'editContract(' . $idc . ')'
        );

        /* ============================================
           DESCARGAR
        ============================================ */

        echo botonAccion(
            'bi-download',
            'Descargar',
            'bg-emerald-500/90 shadow-emerald-500/20 hover:bg-emerald-400',
            'descargarContrato(' . $idc . ')'
        );

        /* ============================================
           CREAR
        ============================================ */

        echo botonAccion(
            'bi-person-plus',
            'Crear',
            'bg-cyan-500/90 shadow-cyan-500/20 hover:bg-cyan-400',
            'addContract(' . $idc . ')'
        );

        /* ============================================
           REENVIAR CONTRATO POR CORREO
        ============================================ */

        echo botonAccion(
            'bi-envelope-arrow-up',
            'Reenviar',
            'bg-indigo-500/90 shadow-indigo-500/20 hover:bg-indigo-400',
            'abrirModalReenviarContrato(' . $idc . ')'
        );

        /* ============================================
           UBICACIÓN
        ============================================ */

        if ($tieneUbicacion) {

            echo '
            <button
                type="button"

                class="btn-ver-ubicacion-contrato min-h-[46px] w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-sky-500/90 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/20 transition hover:bg-sky-400"

                data-id="' . $idc . '"
                data-nombre="' . $nombreData . '"
                data-lat="' . h($lat) . '"
                data-lng="' . h($lng) . '"
                data-precision="' . h($precision) . '"
                data-fuente="' . h($fuente) . '"
                data-fecha="' . h($fechaUbicacion) . '"

                title="Ver ubicación"
            >
                <i class="bi bi-geo-alt-fill text-lg"></i>
                Ubicación
            </button>
            ';

        } else {

            echo '
            <button
                type="button"
                disabled

                class="min-h-[46px] w-full inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-2xl bg-slate-700/60 px-4 py-3 text-sm font-bold text-slate-400"

                title="Este contrato no tiene ubicación guardada"
            >
                <i class="bi bi-geo-alt text-lg"></i>
                Sin ubicación
            </button>
            ';
        }

        /* ============================================
           BOTONES SEGÚN ESTADO
        ============================================ */

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

        /* ============================================
           CERRAR FILA DESPLEGABLE
        ============================================ */

        echo '
                </div>

            </td>

        </tr>
        ';
    }

    /* ============================================
       CIERRE DE TABLA
    ============================================ */

    echo '
            </tbody>
        </table>
    </div>
    ';

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

    </div>
    ';
}

$conexion->close();
?>