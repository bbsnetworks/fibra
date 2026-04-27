<?php 

include_once("conexion.php");
// Verificar si se ha enviado el id del contrato a eliminar

    $idcontrato = $_POST['id'];
    //echo''.$idcontrato.'';

    // Preparar la declaración SQL para eliminar el contrato
    $sql = "DELETE FROM contratos WHERE idcontrato = ?";

    // Preparar la declaración
    if ($stmt = $conexion->prepare($sql)) {
        // Vincular parámetros
        $stmt->bind_param("i", $idcontrato);

        // Ejecutar la declaración
        if ($stmt->execute()) {
            echo "Contrato eliminado exitosamente.";
        } else {
            echo "Error al eliminar el contrato: " . $stmt->error;
        }

        // Cerrar la declaración
        $stmt->close();
    } else {
        echo "Error al preparar la declaración: " . $conexion->error;
    }


// Cerrar la conexión
$conexion->close();

?>