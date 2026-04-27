<?php
include("conexion.php");

if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
    }


    $scosto1="";
    $scosto2= "";
    $fcosto1="";
    $fcosto2= "";

    if($_POST['scosto1']==null || $_POST['scosto1']==""){
        $scosto1=0;
    }else{
        $scosto1=$_POST['scosto1'];
    }
    if($_POST['scosto2']==null || $_POST['scosto2']==""){
        $scosto2=0;
    }else{
        $scosto2=$_POST["scosto2"];
    }
    if($_POST['fcosto1']==null || $_POST['fcosto1']==""){
        $fcosto1=0;
    }else{
        $fcosto1=$_POST["fcosto1"];
    }
    if($_POST['fcosto2']==null || $_POST['fcosto2']==""){
        $fcosto2=0;
    }else{
        $fcosto2=$_POST["fcosto2"];
    }
    $query = "INSERT INTO contratos (
        idcontrato, nombre, rlegal, calle, numero, colonia, municipio, cp, estado, telefono, ttelefono, rfc, fecha, tarifa, tmensualidad, reconexion, mdesconexion, plazo, modeme, marca, modelo, nserie, nequipo, pagoum, pequipo, domicilioi, fechai, hora, costoi, autorizacion, mpago, vigencia, banco, notarjeta, sadicional1, dadicional1, costoa1, sadicional2, dadicional2, costoa2, sfacturable1, dfacturable1, costof1, sfacturable2, dfacturable2, costof2, ccontrato, cderechos, cciudad, firma1, firma2
    ) VALUES (
        $_POST[ncontrato], '$_POST[nombre]', '$_POST[rlegal]', '$_POST[calle]', '$_POST[numero]', '$_POST[colonia]', '$_POST[municipio]', '$_POST[cp]', '$_POST[estado]', '$_POST[telefono]', '$_POST[ttipo]', '$_POST[rfc]', '$_POST[fechac]', '$_POST[tarifa]', $_POST[total], '$_POST[reconexion]', $_POST[mdesco], $_POST[plazo], '$_POST[modemt]', '$_POST[marca]', '$_POST[modelo]', '$_POST[serie]', $_POST[nequipos], '$_POST[tpago]', $_POST[cequipos], '$_POST[domicilioi]', '$_POST[fechai]', '$_POST[horai]', '$_POST[costoi]', '$_POST[acargo]', '$_POST[mpago]', $_POST[cmes], '$_POST[banco]', '$_POST[ntarjeta]', '$_POST[sadicional1]', '$_POST[sdescripcion1]', $scosto1, '$_POST[sadicional2]', '$_POST[sdescripcion2]', $scosto2, '$_POST[fadicional1]', '$_POST[fdescripcion1]', $fcosto1, '$_POST[fadicional2]', '$_POST[fdescripcion2]', $fcosto2, $_POST[ccontrato], $_POST[cdminimos], '$_POST[ciudad]', '$_POST[firma1]', '$_POST[firma2]'
    )";
    //echo $query;

    //  echo $query;
    // die();
    
    if ($conexion->query($query) === TRUE) {
        // Si la inserción es exitosa, procede con el flujo
        //echo 'insercion exitosa';
        
    } else {
        // Si hay un error, muestra el mensaje de error
        echo "Error: " . $query . "<br>" . $conexion->error;
    }
    
    
    
        $conexion->close();
    
    ?>