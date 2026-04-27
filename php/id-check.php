<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

try {
    if (!isset($_POST['ncontrato']) || $_POST['ncontrato'] === '') {
        throw new Exception('Número no recibido.');
    }

    $idInput = (int) $_POST['ncontrato'];

    $stmt1 = $conexion->prepare("SELECT COUNT(*) FROM clientes WHERE idcliente = ?");
    $stmt1->bind_param("i", $idInput);
    $stmt1->execute();
    $stmt1->bind_result($countCliente);
    $stmt1->fetch();
    $stmt1->close();

    $stmt2 = $conexion->prepare("SELECT COUNT(*) FROM contratos WHERE idcontrato = ?");
    $stmt2->bind_param("i", $idInput);
    $stmt2->execute();
    $stmt2->bind_result($countContrato);
    $stmt2->fetch();
    $stmt2->close();

    echo json_encode([
        'ok' => true,
        'exists_cliente' => $countCliente > 0,
        'exists_contrato' => $countContrato > 0,
        'exists' => ($countCliente > 0 || $countContrato > 0)
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ]);
}