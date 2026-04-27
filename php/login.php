<?php
session_start();

$servername = "b88e0bd2df17.sn.mynetname.net:3306";
$username = "adminbbs";
$password = "Admin_Pinck";
$dbname = "sysbbs";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = sha1($_POST['password']); // Encriptamos la contraseña con MD5

    //$sql = "SELECT id FROM users WHERE username = '$username' AND password = '$password'";
    $sql = "SELECT iduser FROM users WHERE nombre = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Usuario y contraseña correctos
        $_SESSION['username'] = $username;
        header("Location: ../index.php"); // Redirigir a una página de bienvenida o dashboard
    } else {
        // Usuario o contraseña incorrectos
        //echo "Usuario o contraseña incorrectos";
        header("Location: ../php/incorrecto.php");

    }
}

$conn->close();
?>
