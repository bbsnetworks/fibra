<?php
header('Content-Type: application/json; charset=utf-8');

// Validar que el PDF existe
if (!isset($_FILES['pdf'])) {
    echo json_encode(["ok" => false, "message" => "No se recibió el archivo PDF."]);
    exit;
}

$destino = $_SERVER['DOCUMENT_ROOT'] . "/contratos/pdf/contrato" . $_POST['idcontrato'] . ".pdf";

if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $destino)) {
    echo json_encode(["ok" => false, "message" => "No se pudo guardar el archivo PDF."]);
    exit;
}

// Incluir y ejecutar inserción en base de datos
ob_start(); // Captura salida de insert.php (por si tiene echo o errores)
include("insert.php");
$salida = ob_get_clean();

// Si insert.php generó texto, lo mostramos como error
if (trim($salida) !== "") {
    echo json_encode(["ok" => false, "message" => "Error en inserción: $salida"]);
    exit;
}

// Todo bien
echo json_encode(["ok" => true, "message" => "Contrato guardado y PDF generado."]);
exit;
?>
