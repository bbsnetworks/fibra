<?php
include("conexion.php");

header('Content-Type: application/json; charset=utf-8');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "ID de contrato no válido."
    ]);
    exit;
}

/**
 * Normaliza la firma para que SIEMPRE salga como:
 * data:image/png;base64,xxxxx
 * o data:image/jpeg;base64,xxxxx
 */
function normalizarFirma($firma) {
    if (empty($firma)) {
        return "";
    }

    // Asegurarnos de tratarlo como string
    $firmaStr = (string)$firma;

    // Caso 1: ya viene guardada como dataURL completa
    // Ejemplo: data:image/png;base64,iVBORw0...
    if (strpos($firmaStr, 'data:image') === 0) {
        return $firmaStr;
    }

    // Detectar tipo real por firma binaria
    $inicioHex = strtoupper(bin2hex(substr($firma, 0, 8)));

    // PNG => 89 50 4E 47 0D 0A 1A 0A
    if (strpos($inicioHex, '89504E47') === 0) {
        return 'data:image/png;base64,' . base64_encode($firma);
    }

    // JPG/JPEG => FF D8 FF
    if (strpos($inicioHex, 'FFD8FF') === 0) {
        return 'data:image/jpeg;base64,' . base64_encode($firma);
    }

    // Si por alguna razón viene base64 limpio sin encabezado
    if (preg_match('/^[A-Za-z0-9+\/=\r\n]+$/', $firmaStr)) {
        $firmaLimpia = preg_replace('/\s+/', '', $firmaStr);
        return 'data:image/png;base64,' . $firmaLimpia;
    }

    // Fallback: asumir PNG binario
    return 'data:image/png;base64,' . base64_encode($firma);
}

$sql = "SELECT * FROM contratos WHERE idcontrato = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al preparar consulta: " . $conexion->error
    ]);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "No se encontró el contrato."
    ]);
    exit;
}

$data = $result->fetch_assoc();

/* Normalizar firmas */
$data['firma1'] = normalizarFirma($data['firma1'] ?? null);

/* firma2 es opcional */
$data['firma2'] = normalizarFirma($data['firma2'] ?? null);

/* Evitar problemas con NULL */
foreach ($data as $key => $value) {
    if ($value === null) {
        $data[$key] = "";
    }
}

$stmt->close();
$conexion->close();

$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

if ($json === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al convertir datos a JSON: " . json_last_error_msg()
    ]);
    exit;
}

echo $json;
?>