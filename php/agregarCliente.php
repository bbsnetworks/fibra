<?php
include('conexion.php');

if ($conexion->connect_error) {
    die('Conexión fallida: ' . $conexion->connect_error);
}

$idcontrato = (int)($_POST['id'] ?? 0);

$sql  = "SELECT * FROM contratos WHERE idcontrato = $idcontrato";
$sql2 = "SELECT * FROM nodos";
$sql3 = "SELECT * FROM localidad";

$result  = $conexion->query($sql);
$result2 = $conexion->query($sql2);
$result3 = $conexion->query($sql3);

$name = '';
$email = '';

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name  = $row["nombre"] ?? '';
    $email = $row["correo_electronico"] ?? '';
}

echo "
<div class='row'>
  <form id='userForm'>

    <input type='hidden' id='email' name='email' value='" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "'>

    <div class='col-4'>
      <label class='form-label'>ID:</label>
      <input type='text' class='form-control' id='id' name='id' disabled value='" . htmlspecialchars($idcontrato, ENT_QUOTES, 'UTF-8') . "'>
    </div>

    <div class='col-8'>
      <label class='form-label'>Nombre:</label>
      <input type='text' class='form-control' id='nombre' name='nombre' disabled value='" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "'>
    </div>

    <div class='col-lg-6'>
      <label class='form-label'>Localidad</label>
      <select id='localidad' class='form-select' name='localidad' required>
        <option value='' selected>Seleccionar Localidad</option>";

if ($result3 && $result3->num_rows > 0) {
    while ($row = $result3->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['idlocalidad'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['nombrelocalidad'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
} else {
    echo "<option value=''>No hay localidades disponibles</option>";
}

echo "
      </select>
    </div>

    <div class='col-lg-6'>
      <label class='form-label'>Nodo</label>
      <select id='nodo' class='form-select' name='nodo' required>
        <option value='' selected>Seleccionar Nodo</option>";

if ($result2 && $result2->num_rows > 0) {
    while ($row = $result2->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($row['idnodo'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
} else {
    echo "<option value=''>No hay nodos disponibles</option>";
}

echo "
      </select>
    </div>

    <div class='col-lg-6'>
      <label class='form-label'>IP</label>
      <input 
        type='text' 
        class='form-control' 
        id='ip' 
        name='ip' 
        pattern='\\b(?:\\d{1,3}\\.){3}\\d{1,3}\\b' 
        title='Por favor, ingrese una dirección IP válida. Ejemplo: 192.168.0.1' 
        required>
    </div>

    <div class='col-lg-6'>
      <label class='form-label'>Splitter</label>
      <input type='text' class='form-control' id='splitter' name='splitter'>
    </div>

    <div class='col-12 mt-2'>
      <small class='text-muted'>
        El correo se tomará automáticamente del contrato: 
        <strong>" . htmlspecialchars($email ?: 'Sin correo registrado', ENT_QUOTES, 'UTF-8') . "</strong>
      </small>
    </div>

    <div class='col-2 mt-3'>
      <button type='button' class='btn btn-info' onclick='validateAndAddUsuario(" . $idcontrato . ")'>
        Agregar
      </button>
    </div>

  </form>
</div>
";
?>