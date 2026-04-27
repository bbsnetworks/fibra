<?php
include("conexion.php");
    $idcontrato=$_GET['id'];
    $nodo=$_GET['nodo'];
    $localidad=$_GET['localidad'];
    $ip=$_GET['ip'];
    $email=$_GET['email'];
    $splitter=$_GET['splitter'];
    //echo $idcontrato;
    $nombre = "";
    $direccion="";
    $mensualidad= "";
    $telefono="";
    $tarifa= "";
    $instalacion= "";

    // echo $idcontrato;
    // echo "<br>";
    // echo $nodo;
    // echo "<br>";
    // echo $localidad;
    // echo "<br>";
    // echo $ip;
    // echo "<br>";
    // echo $email;
    // echo "<br>";
    // die();

if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
    }
    $sql2="SELECT * FROM contratos where idcontrato=".$idcontrato;

    $result2 = $conexion->query($sql2);

    if ($result2->num_rows > 0) {
      // Output data of each row
      
      while ($row2 = $result2->fetch_assoc()) {
       
        $nombre = $row2['nombre'];
        $direccion= $row2['calle'];
        $numero = $row2['numero'];
        $colonia = $row2['colonia'];
        $cp = $row2['cp'];
        $mensualidad= $row2['tmensualidad'];
        $telefono= $row2['telefono'];
        $instalacion= $row2['fecha'];
        $completa = $direccion . ' ' . $numero;

        if ($row2['modeme']== '1') {
          $modeme="COMODATO";  
      }else if ($row2["modeme"]== '2') {
        $modeme= 'COMPRAVENTA';
        }

        if ($row2['tarifa']== '1') {
            $tarifa= '7 Mbps';
        }else if($row2['tarifa']== '2'){
            $tarifa= '10 Mbps';
        } else if($row2['tarifa']== '3'){
            $tarifa= '15 Mbps';
        }else if($row2['tarifa']== '4'){
            $tarifa= '20 Mbps';
        }else if($row2['tarifa']== '5'){
            $tarifa= '40 Mbps';
        }else if($row2['tarifa']== '6'){
            $tarifa= '50 Mbps';
        }           
    }
}
    $query = "insert into clientes (idcliente,nombre, direccion, localidad, nodo, ip, mensualidad, equipo, telefono, email, paquete, instalacion,splitter) values 
    ('$idcontrato','$nombre','$completa','$localidad','$nodo','$ip','$mensualidad','$modeme','$telefono','$email','$tarifa','$instalacion','$splitter')";
    

    //  echo $query;
    // die();
    
    if ($conexion->query($query) === TRUE) {
        // Si la inserción es exitosa, procede con el flujo
        echo 'insercion exitosa';
        
    } else {
        // Si hay un error, muestra el mensaje de error
        //echo "Error: " . $query . "<br>" . $conexion->error;
        echo "error";
    }
    
    
    
        $conexion->close();
    
    ?>