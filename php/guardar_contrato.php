<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conexion->set_charset('utf8mb4');

function post_str(string $key, string $default = ''): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function post_int(string $key, int $default = 0): int {
    $value = post_str($key, '');
    if ($value === '') return $default;
    $value = preg_replace('/[^\d\-]/', '', $value);
    return ($value === '' || !is_numeric($value)) ? $default : (int)$value;
}

function post_float(string $key, float $default = 0): float {
    $value = post_str($key, '');
    if ($value === '') return $default;

    // Limpia $, comas y espacios
    $value = str_replace(['$', ',', ' '], '', $value);

    return is_numeric($value) ? (float)$value : $default;
}

function post_bool_tinyint(string $key): int {
    $value = strtolower(post_str($key, ''));
    return in_array($value, ['1', 'true', 'on', 'si', 'sí', 'yes'], true) ? 1 : 0;
}

function post_si_no_to_tinyint(string $key): int {
    $value = strtolower(post_str($key, ''));
    return $value === 'si' || $value === 'sí' ? 1 : 0;
}

function post_optional_null(string $key): ?string {
    $value = post_str($key, '');
    return $value === '' ? null : $value;
}

function decode_data_url(?string $dataUrl): ?string {
    if (!$dataUrl) return null;

    if (preg_match('/^data:image\/\w+;base64,/', $dataUrl)) {
        $dataUrl = preg_replace('/^data:image\/\w+;base64,/', '', $dataUrl);
    }

    $binary = base64_decode($dataUrl, true);
    return ($binary === false) ? null : $binary;
}

function build_metodos_pago(): array {
    $tipo = post_str('metodoPago', '');

    $map = [
        'efectivo'      => '1',
        'tarjeta'       => '2',
        'transferencia' => '3',
        'deposito'      => '4',
        'tiendas'       => '5',
        'domiciliado'   => '6',
        'enlinea'       => '7',
        'centros'       => '8',
    ];

    $codigo = $map[$tipo] ?? '';

    // Como ahora solo puede ser uno, ambos pueden guardar el mismo código
    $mpago = $codigo !== '' ? $codigo : null;
    $metodosPago = $codigo !== '' ? $codigo : null;

    return [$mpago, $metodosPago];
}

function bind_dynamic_params(mysqli_stmt $stmt, array &$params): void {
    $types = '';

    foreach ($params as $value) {
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } else {
            $types .= 's';
        }
    }

    $bind = [$types];
    foreach ($params as $k => &$v) {
        $bind[] = &$v;
    }

    call_user_func_array([$stmt, 'bind_param'], $bind);
}
function guardar_evidencias_contrato(int $idcontrato): ?string {
    if (
        !isset($_FILES['documentosContrato']) ||
        empty($_FILES['documentosContrato']['name'][0])
    ) {
        return null;
    }

    $carpeta = __DIR__ . '/../evidencia/';

    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }

    $permitidos = ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx'];
    $rutas = [];

    foreach ($_FILES['documentosContrato']['name'] as $i => $nombreOriginal) {
        if ($_FILES['documentosContrato']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }

        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if (!in_array($extension, $permitidos, true)) {
            continue;
        }

        $numero = $i + 1;
        $nombreArchivo = "evidencia{$idcontrato}-{$numero}.{$extension}";
        $rutaDestino = $carpeta . $nombreArchivo;

        if (move_uploaded_file($_FILES['documentosContrato']['tmp_name'][$i], $rutaDestino)) {
            $rutas[] = "evidencia/" . $nombreArchivo;
        }
    }

    return count($rutas) > 0 ? implode(',', $rutas) : null;
}
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $idcontrato = post_int('ncontrato');
    if ($idcontrato <= 0) {
        throw new Exception('Número de contrato inválido.');
    }

    // Nombre completo
    $nombre = post_str('nombre');
    $apellidoPaterno = post_str('apellidoPaterno');
    $apellidoMaterno = post_str('apellidoMaterno');
    $nombreCompleto = trim(preg_replace('/\s+/', ' ', $nombre . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno));

    if ($nombreCompleto === '') {
        throw new Exception('El nombre del suscriptor es obligatorio.');
    }

    // Número exterior + interior
    $numeroExterior = post_str('numeroExterior');
    $numeroInterior = post_str('numeroInterior');
    $numeroCompleto = trim(preg_replace('/\s+/', ' ', $numeroExterior . ' ' . $numeroInterior));

    // Métodos de pago
    [$mpagoCompat, $metodosPago] = build_metodos_pago();

    // Firmas
    $firma1 = decode_data_url(post_optional_null('firma'));
    $firma2 = decode_data_url(post_optional_null('firma2')); // si más adelante agregas segunda firma

    // Evidencia / documentos adjuntos
    $evidencia = guardar_evidencias_contrato($idcontrato);

    // Campos derivados / compatibilidad
    $tipoEntregaEquipo = post_str('tipoEntregaEquipo');
    $modeme = $tipoEntregaEquipo; // para no dejarlo vacío y mantener compatibilidad

    $descripcionServicio1 = post_str('servicioAdic1Desc');
    $descripcionServicio2 = post_str('servicioAdic2Desc');
    $descripcionFact1 = post_str('conceptoFact1Desc');
    $descripcionFact2 = post_str('conceptoFact2Desc');

    // Defaults de control
    $status = 'activo';
    $fechaCancelacion = null;
    $canceladoPor = null;
    $folioCancelacion = null;
    $esLegado = post_bool_tinyint('es_legado'); // si no mandas nada será 0
    $equiposDevueltos = null;

    $data = [
        'idcontrato'                 => $idcontrato,
        'nombre'                     => $nombreCompleto,
        'rlegal'                     => $nombreCompleto,
        'calle'                      => post_str('calle'),
        'numero'                     => $numeroCompleto,
        'colonia'                    => post_str('colonia'),
        'municipio'                  => post_str('municipio'),
        'cp'                         => post_str('cp'),
        'estado'                     => post_str('estado'),
        'equipos_devueltos'          => $equiposDevueltos,
        'telefono'                   => post_str('telefono'),
        'ttelefono'                  => post_str('tipoTelefono'),
        'rfc'                        => post_optional_null('rfc'),
        'fecha'                      => post_str('fechac'),
        'tarifa'                     => post_str('descripcionPaquete'),
        'tmensualidad'               => post_float('mensualidad', 0),
        'reconexion'                 => post_str('aplicaReconexcion'),
        'mdesconexion'               => post_int('montoReconexcion', 0),
        'nom_numeral'                => post_optional_null('nomNumeral'),
        'penalidad_texto'            => post_optional_null('penalidadTexto'),
        'plazo'                      => post_int('mesesPlazo', 0),
        'tipo_entrega_equipo'        => post_optional_null('tipoEntregaEquipo'),
        'modeme'                     => $modeme,
        'marca'                      => post_str('marcaEquipo'),
        'modelo'                     => post_str('modeloEquipo'),
        'nserie'                     => post_str('numeroSerie'),
        'nequipo'                    => post_int('numeroEquipos', 0),
        'pagoum'                     => post_optional_null('modalidadPagoEquipo'),
        'pequipo'                    => post_float('costoTotalEquipo', 0),
        'costo_diferido'             => post_float('costoDiferido', 0),
        'meses_diferido'             => post_int('mesesDiferido', 0),
        'domicilioi'                 => post_str('domicilioInstalacion'),
        'fechai'                     => post_str('fechaInstalacion'),
        'hora'                       => post_str('horaInstalacion'),
        'costoi'                     => post_optional_null('costoInstalacion'),
        'autorizacion'               => post_str('autorizaCargoTarjeta'),
        'mpago'                      => $mpagoCompat,
        'metodos_pago'               => $metodosPago !== '' ? $metodosPago : null,
        'datos_metodo_pago'          => post_optional_null('datosMetodoPago'),
        'vigencia'                   => post_int('mesesCargoTarjeta', 0),
        'tipo_vigencia'              => post_optional_null('tipoVigencia'),
        'banco'                      => post_optional_null('banco'),
        'notarjeta'                  => post_optional_null('numeroTarjeta'),
        'correo_electronico'         => post_optional_null('correoElectronico'),
        'otro_medio_electronico'     => post_optional_null('otroMedioElectronico'),
        'numero_otro_medio'          => post_optional_null('numeroOtroMedio'),
        'sadicional1'                => $descripcionServicio1 !== '' ? $descripcionServicio1 : null,
        'dadicional1'                => $descripcionServicio1 !== '' ? $descripcionServicio1 : null,
        'costoa1'                    => post_float('servicioAdic1Costo', 0),
        'sadicional2'                => $descripcionServicio2 !== '' ? $descripcionServicio2 : null,
        'dadicional2'                => $descripcionServicio2 !== '' ? $descripcionServicio2 : null,
        'costoa2'                    => post_float('servicioAdic2Costo', 0),
        'sfacturable1'               => $descripcionFact1 !== '' ? $descripcionFact1 : null,
        'dfacturable1'               => $descripcionFact1 !== '' ? $descripcionFact1 : null,
        'costof1'                    => post_float('conceptoFact1Costo', 0),
        'sfacturable2'               => $descripcionFact2 !== '' ? $descripcionFact2 : null,
        'dfacturable2'               => $descripcionFact2 !== '' ? $descripcionFact2 : null,
        'costof2'                    => post_float('conceptoFact2Costo', 0),
        'cfactura'                   => post_si_no_to_tinyint('envioFactura'),
        'ccontrato'                  => post_si_no_to_tinyint('envioContratoAdhesion'),
        'cderechos'                  => post_si_no_to_tinyint('envioCartaDerechos'),
        'autoriza_ceder_info'        => post_si_no_to_tinyint('autorizaCederInfo'),
        'autoriza_llamadas_promo'    => post_si_no_to_tinyint('autorizaLlamadasPromo'),
        'acepta_contrato'            => post_bool_tinyint('aceptaContratoFibra'),
        'cciudad'                    => post_str('ciudadFirma'),
        'dia_firma'                  => post_optional_null('diaFirma'),
        'mes_firma'                  => post_optional_null('mesFirma'),
        'anio_firma'                 => post_optional_null('anioFirma'),
        'firma1'                     => $firma1,
        'firma2'                     => $firma2,
        'evidencia'                  => $evidencia,
        'status'                     => $status,
        'fecha_cancelacion'          => $fechaCancelacion,
        'cancelado_por'              => $canceladoPor,
        'folio_cancelacion'          => $folioCancelacion,
        'es_legado'                  => $esLegado,
    ];

    $columns = array_keys($data);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO contratos (" . implode(', ', $columns) . ") VALUES ($placeholders)";

    $stmt = $conexion->prepare($sql);
    $params = array_values($data);
    bind_dynamic_params($stmt, $params);
    $stmt->execute();

    echo json_encode([
        'ok' => true,
        'message' => 'Contrato guardado correctamente.',
        'idcontrato' => $idcontrato
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ]);
}