<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

try {
    if (!isset($_POST['numero']) || $_POST['numero'] === '') {
        throw new Exception('Número no recibido.');
    }

    $numero = (int) $_POST['numero'];

    // 1. Validar cliente
    $stmt1 = $conexion->prepare("SELECT COUNT(*) FROM clientes WHERE idcliente = ?");
    $stmt1->bind_param("i", $numero);
    $stmt1->execute();
    $stmt1->bind_result($countCliente);
    $stmt1->fetch();
    $stmt1->close();

    // 2. Validar contrato (ajusta nombre de tabla si es diferente)
    $stmt2 = $conexion->prepare("SELECT COUNT(*) FROM contratos WHERE idcontrato = ?");
    $stmt2->bind_param("i", $numero);
    $stmt2->execute();
    $stmt2->bind_result($countContrato);
    $stmt2->fetch();
    $stmt2->close();

    echo json_encode([
        'ok' => true,
        'clienteExiste' => $countCliente > 0,
        'contratoExiste' => $countContrato > 0,
        'disponible' => ($countCliente == 0 && $countContrato == 0)
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ]);
}