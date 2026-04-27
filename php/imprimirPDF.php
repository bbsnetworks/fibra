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

/* Convertir BLOBs a base64 para que JSON no falle */
if (!empty($data['firma1'])) {
    $data['firma1'] = base64_encode($data['firma1']);
} else {
    $data['firma1'] = "";
}

if (!empty($data['firma2'])) {
    $data['firma2'] = base64_encode($data['firma2']);
} else {
    $data['firma2'] = "";
}

/* Evitar problemas con NULL */
foreach ($data as $key => $value) {
    if ($value === null) {
        $data[$key] = "";
    }
}

$stmt->close();
$conexion->close();

$json = json_encode($data, JSON_UNESCAPED_UNICODE);

if ($json === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Error al convertir datos a JSON: " . json_last_error_msg()
    ]);
    exit;
}

echo $json;
?>