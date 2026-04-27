<?php
include("conexion.php");

header('Content-Type: application/json; charset=utf-8');

if ($conexion->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Connection failed: " . $conexion->connect_error
    ]);
    exit;
}

/* =========================
   Helpers
========================= */
function esc($conexion, $key, $default = '')
{
    return $conexion->real_escape_string($_POST[$key] ?? $default);
}

function numOrZero($conexion, $key)
{
    $value = trim((string)($_POST[$key] ?? '0'));
    if ($value === '') {
        $value = '0';
    }
    // Quita símbolos comunes por si llegan montos tipo $500
    $value = str_replace([',', '$', ' '], '', $value);
    if (!is_numeric($value)) {
        $value = '0';
    }
    return $conexion->real_escape_string($value);
}

function intOrZero($key)
{
    return (int)($_POST[$key] ?? 0);
}

/* =========================
   Checkboxes
========================= */
$ccontrato               = !empty($_POST['ccontrato']) ? "1" : "0";
$cderechos               = !empty($_POST['cderechos']) ? "1" : "0";
$autoriza_ceder_info     = !empty($_POST['autoriza_ceder_info']) ? "1" : "0";
$autoriza_llamadas_promo = !empty($_POST['autoriza_llamadas_promo']) ? "1" : "0";
$acepta_contrato         = !empty($_POST['acepta_contrato']) ? "1" : "0";

/* =========================
   Campos base
========================= */
$nombre        = esc($conexion, 'nombre');
$rlegal        = esc($conexion, 'rlegal');
$calle         = esc($conexion, 'calle');
$numero        = esc($conexion, 'numero');
$colonia       = esc($conexion, 'colonia');
$municipio     = esc($conexion, 'municipio');
$cp            = esc($conexion, 'cp');
$estado        = esc($conexion, 'estado');
$telefono      = esc($conexion, 'telefono');
$ttipo         = esc($conexion, 'ttipo');
$rfc           = esc($conexion, 'rfc');
$fechac        = esc($conexion, 'fechac');
$tarifa        = esc($conexion, 'tarifa');
$total         = numOrZero($conexion, 'total');
$reconexion    = esc($conexion, 'reconexion');
$mdesco        = numOrZero($conexion, 'mdesco');

/* =========================
   Nuevos de servicio
========================= */
$nom_numeral     = esc($conexion, 'nom_numeral');
$penalidad_texto = esc($conexion, 'penalidad_texto');
$plazo           = intOrZero('plazo');
$tipo_vigencia   = esc($conexion, 'tipo_vigencia');

/* =========================
   Equipo
========================= */
$modemt              = esc($conexion, 'modemt');
$tipo_entrega_equipo = esc($conexion, 'tipo_entrega_equipo');
$marca               = esc($conexion, 'marca');
$modelo              = esc($conexion, 'modelo');
$serie               = esc($conexion, 'serie');
$nequipos            = intOrZero('nequipos');
$tpago               = esc($conexion, 'tpago');
$cequipos            = numOrZero($conexion, 'cequipos');
$costo_diferido      = numOrZero($conexion, 'costo_diferido');
$meses_diferido      = intOrZero('meses_diferido');

/* =========================
   Instalación
========================= */
$domicilioi = esc($conexion, 'domicilioi');
$fechai     = esc($conexion, 'fechai');
$horai      = esc($conexion, 'horai');
$costoi     = esc($conexion, 'costoi');

/* =========================
   Pago
========================= */
$acargo                 = esc($conexion, 'acargo');
$mpago                  = esc($conexion, 'mpago');
$cmes                   = intOrZero('cmes');
$banco                  = esc($conexion, 'banco');
$ntarjeta               = esc($conexion, 'ntarjeta');
$datos_metodo_pago      = esc($conexion, 'datos_metodo_pago');
$correo_electronico     = esc($conexion, 'correo_electronico');
$otro_medio_electronico = esc($conexion, 'otro_medio_electronico');
$numero_otro_medio      = esc($conexion, 'numero_otro_medio');

/* metodos_pago puede venir como JSON o como arreglo */
$metodos_pago = $conexion->real_escape_string($_POST['metodos_pago'] ?? '');

if (is_array($metodos_pago)) {
    $metodos_pago = json_encode(array_values($metodos_pago), JSON_UNESCAPED_UNICODE);
} else {
    $metodos_pago = trim((string)$metodos_pago);

    $decoded = json_decode($metodos_pago, true);
    if (!is_array($decoded)) {
        if ($metodos_pago === '') {
            $metodos_pago = json_encode([], JSON_UNESCAPED_UNICODE);
        } else {
            $metodos_pago = json_encode(
                array_filter(array_map('trim', explode(',', $metodos_pago))),
                JSON_UNESCAPED_UNICODE
            );
        }
    }
}
$metodos_pago = $conexion->real_escape_string($metodos_pago);

/* =========================
   Adicionales
========================= */
$sadicional1   = esc($conexion, 'sadicional1');
$sdescripcion1 = esc($conexion, 'sdescripcion1');
$scosto1       = numOrZero($conexion, 'scosto1');
$sadicional2   = esc($conexion, 'sadicional2');
$sdescripcion2 = esc($conexion, 'sdescripcion2');
$scosto2       = numOrZero($conexion, 'scosto2');

$fadicional1   = esc($conexion, 'fadicional1');
$fdescripcion1 = esc($conexion, 'fdescripcion1');
$fcosto1       = numOrZero($conexion, 'fcosto1');
$fadicional2   = esc($conexion, 'fadicional2');
$fdescripcion2 = esc($conexion, 'fdescripcion2');
$fcosto2       = numOrZero($conexion, 'fcosto2');

/* =========================
   Firma / ciudad
========================= */
$ciudad     = esc($conexion, 'ciudad');
$dia_firma  = esc($conexion, 'dia_firma');
$mes_firma  = esc($conexion, 'mes_firma');
$anio_firma = esc($conexion, 'anio_firma');

/* =========================
   Cancelación
========================= */
$equipos_devueltos = esc($conexion, 'equipos_devueltos');

$fecha_cancelacion = $_POST['fecha_cancelacion'] ?? '';
$fecha_cancelacion_sql = "NULL";
if ($fecha_cancelacion !== '') {
    $fecha_cancelacion_norm = str_replace('T', ' ', $fecha_cancelacion);
    if (strlen($fecha_cancelacion_norm) === 16) {
        $fecha_cancelacion_norm .= ':00';
    }
    $fecha_cancelacion_sql = "'" . $conexion->real_escape_string($fecha_cancelacion_norm) . "'";
}

/* =========================
   ID contrato
========================= */
$idcontrato = (int)($_POST['ncontrato'] ?? 0);

if ($idcontrato <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "ID de contrato no válido."
    ]);
    exit;
}

/* =========================
   UPDATE
========================= */
$query = "
UPDATE contratos SET
    nombre = '$nombre',
    rlegal = '$rlegal',
    calle = '$calle',
    numero = '$numero',
    colonia = '$colonia',
    municipio = '$municipio',
    cp = '$cp',
    estado = '$estado',
    telefono = '$telefono',
    ttelefono = '$ttipo',
    rfc = '$rfc',
    fecha = '$fechac',

    tarifa = '$tarifa',
    tmensualidad = $total,
    reconexion = '$reconexion',
    mdesconexion = $mdesco,
    nom_numeral = '$nom_numeral',
    penalidad_texto = '$penalidad_texto',
    plazo = $plazo,
    tipo_vigencia = '$tipo_vigencia',

    modeme = '$modemt',
    tipo_entrega_equipo = '$tipo_entrega_equipo',
    marca = '$marca',
    modelo = '$modelo',
    nserie = '$serie',
    nequipo = $nequipos,
    pagoum = '$tpago',
    pequipo = $cequipos,
    costo_diferido = $costo_diferido,
    meses_diferido = $meses_diferido,

    domicilioi = '$domicilioi',
    fechai = '$fechai',
    hora = '$horai',
    costoi = '$costoi',

    autorizacion = '$acargo',
    mpago = '$mpago',
    vigencia = $cmes,
    metodos_pago = '$metodos_pago',
    datos_metodo_pago = '$datos_metodo_pago',
    banco = '$banco',
    notarjeta = '$ntarjeta',
    correo_electronico = '$correo_electronico',
    otro_medio_electronico = '$otro_medio_electronico',
    numero_otro_medio = '$numero_otro_medio',

    sadicional1 = '$sadicional1',
    dadicional1 = '$sdescripcion1',
    costoa1 = $scosto1,
    sadicional2 = '$sadicional2',
    dadicional2 = '$sdescripcion2',
    costoa2 = $scosto2,

    sfacturable1 = '$fadicional1',
    dfacturable1 = '$fdescripcion1',
    costof1 = $fcosto1,
    sfacturable2 = '$fadicional2',
    dfacturable2 = '$fdescripcion2',
    costof2 = $fcosto2,

    ccontrato = $ccontrato,
    cderechos = $cderechos,
    autoriza_ceder_info = $autoriza_ceder_info,
    autoriza_llamadas_promo = $autoriza_llamadas_promo,
    acepta_contrato = $acepta_contrato,

    cciudad = '$ciudad',
    dia_firma = '$dia_firma',
    mes_firma = '$mes_firma',
    anio_firma = '$anio_firma',

    equipos_devueltos = '$equipos_devueltos',
    fecha_cancelacion = $fecha_cancelacion_sql

WHERE idcontrato = $idcontrato
";

/* =========================
   Ejecutar
========================= */
if ($conexion->query($query) === TRUE) {
    echo json_encode([
        "status" => "success",
        "message" => "Contrato actualizado correctamente."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Error al actualizar el contrato: " . $conexion->error
    ]);
}

$conexion->close();