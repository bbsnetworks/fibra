<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

try {
    $sql = "
        SELECT GREATEST(
            COALESCE((SELECT MAX(idcliente) FROM clientes), 0),
            COALESCE((SELECT MAX(idcontrato) FROM contratos), 0)
        ) + 1 AS siguiente
    ";

    $result = $conexion->query($sql);

    if (!$result) {
        throw new Exception('No se pudo obtener el siguiente número.');
    }

    $row = $result->fetch_assoc();

    echo json_encode([
        'ok' => true,
        'numero' => (int)$row['siguiente']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => $e->getMessage()
    ]);
}