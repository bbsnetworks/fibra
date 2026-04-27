<?php
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_FILES['archivo'])) {
        throw new Exception('No se recibió el archivo.');
    }

    if (!isset($_POST['numero']) || trim($_POST['numero']) === '') {
        throw new Exception('No se recibió el número de contrato.');
    }

    $contraton = trim($_POST['numero']);

    // Ruta absoluta a la carpeta /evidencia en la raíz del proyecto
    $targetDir = dirname(__DIR__) . '/evidencia/';

    // Verificar si existe la carpeta
    if (!is_dir($targetDir)) {
        throw new Exception('La carpeta de evidencia no existe: ' . $targetDir);
    }

    // Verificar permisos de escritura
    if (!is_writable($targetDir)) {
        throw new Exception('La carpeta de evidencia no tiene permisos de escritura.');
    }

    // Validar error de subida
    if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir archivo. Código: ' . $_FILES['archivo']['error']);
    }

    $nombreOriginal = $_FILES['archivo']['name'];
    $tmpName = $_FILES['archivo']['tmp_name'];
    $fileExtension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

    // Extensiones permitidas
    $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    if (!in_array($fileExtension, $permitidas, true)) {
        throw new Exception('Tipo de archivo no permitido.');
    }

    // Limpiar número de contrato para evitar caracteres raros
    $contratonLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '', $contraton);

    $targetFile = $targetDir . 'evidencia' . $contratonLimpio . '.' . $fileExtension;

    if (!move_uploaded_file($tmpName, $targetFile)) {
        throw new Exception('No se pudo mover el archivo a la carpeta destino.');
    }

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Archivo guardado correctamente.',
        'archivo' => 'evidencia' . $contratonLimpio . '.' . $fileExtension
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage()
    ]);
}
