<?php
$servername_pos = 'localhost:3307';
$database_pos   = 'smartgatepos';
$username_pos   = 'root';
$password_pos   = 'root';

$conexion_pos = mysqli_connect(
    $servername_pos,
    $username_pos,
    $password_pos,
    $database_pos
);

mysqli_set_charset($conexion_pos, 'utf8mb4');

if (!$conexion_pos) {
    die(json_encode([
        "ok" => false,
        "message" => "Error de conexión a smartgatepos: " . mysqli_connect_error()
    ], JSON_UNESCAPED_UNICODE));
}
?>