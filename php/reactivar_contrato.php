<?php
require_once "conexion.php";
header('Content-Type: application/json; charset=utf-8');

try {
    $input = json_decode(file_get_contents("php://input"), true) ?? [];
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    if ($id <= 0) throw new Exception('ID de contrato inválido');

    $sql = "UPDATE contratos 
            SET status='activo',
                fecha_cancelacion=NULL,
                cancelado_por=NULL,
                folio_cancelacion=NULL,
                equipos_devueltos=NULL
            WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) throw new Exception('Error en prepare: '.$conexion->error);
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) throw new Exception('Error al reactivar: '.$stmt->error);

    echo json_encode(['ok' => true, 'message' => 'Contrato reactivado correctamente.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
    if (isset($conexion) && $conexion instanceof mysqli) { $conexion->close(); }
}
