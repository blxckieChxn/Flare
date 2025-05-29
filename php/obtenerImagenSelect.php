<?php

include_once "conexion.php";
session_start();

function getSelectedCards(){
    if(!isset($_POST["selectedImages"])){
        return [];
    }

    $selectedImages = json_decode($_POST['selectedImages'], true);

    if(!is_array($selectedImages)){
        return [];
    }

    // Limpiar y filtrar valores no válidos
    $auxArray = array_filter($selectedImages, function($val){
        return is_numeric($val) && $val !== null && $val !== '';
    });

    // Reindexar el array para evitar índices huecos
    $auxArray = array_values($auxArray);

    if(!empty($auxArray)){
        echo "Has seleccionado: " . implode(', ', array_map('htmlspecialchars', $auxArray));
    }

    return $auxArray;
}


function saveSelectedCards($conn, $cantidades, $uid){

    foreach($cantidades as $idCarta => $cantidad){
        $cantidad = intval($cantidad);

        if($cantidad > 0){

            // Obtener cantidad actual
            $proc = $conn->prepare("SELECT f.cantidad FROM formado_por f JOIN inventarios i ON (f.idInventario = i.idInventario) WHERE f.idCarta = ? AND i.uid = ?");
            $proc->bind_param("ii", $idCarta, $uid);
            $proc->execute();
            $proc->store_result();  // Almacena el resultado para poder hacer con $proc aun activo
            $proc->bind_result($cantidadActual);

            if ($proc->num_rows > 0) {
                // Si ya existe la carta, aumentamos la cantidad
                $proc->fetch();
                $nuevaCantidad = max(0, $cantidadActual + $cantidad);
            
                $update = $conn->prepare("UPDATE formado_por SET cantidad = ? WHERE idCarta = ? AND idInventario = (SELECT idInventario FROM inventarios WHERE uid = ? LIMIT 1)");
                $update->bind_param("iii", $nuevaCantidad, $idCarta, $uid);
                $update->execute();
                $update->close();

            } else {
                // Si no existe la carta la insertamos
                $insert = $conn->prepare("INSERT INTO formado_por (idCarta, idInventario, cantidad) VALUES (?, (SELECT idInventario FROM inventarios WHERE uid = ? LIMIT 1), ?)");
                $insert->bind_param("iii", $idCarta, $uid, $cantidad);
                $insert->execute();
                $insert->close();
            }               
            
            $proc->close();
        }
    }

}

function deleteSelectedCards($conn, $auxArray, $uid){
    $j=0;
    $tamañoArrayAux = count($auxArray);

    while($j < $tamañoArrayAux){
        // ID actual de la carta
        $idCarta = $auxArray[$j];
        // Consultas SQL
        $sqlDelete = "DELETE FROM formado_por WHERE idCarta='$idCarta' AND idInventario = (SELECT idInventario FROM inventarios WHERE uid = $uid)";
        $sqlCheck = "SELECT idCarta, cantidad FROM formado_por WHERE idCarta = '$idCarta' AND idInventario = (SELECT idInventario from inventarios where uid = $uid)";
        $sqlUpdate = "UPDATE formado_por SET cantidad = (cantidad - 1) WHERE idCarta = '$idCarta' AND idInventario = (SELECT idInventario from inventarios where uid = $uid)";
        
        // Comprueba si la carta existe en la BD (va a existir siempre, sino no saldria en la página 'my_cards')
        $result = $conn->query($sqlCheck);
        if (mysqli_num_rows($result)>0){ 

            // Extraer datos de la BD
            $row = mysqli_fetch_assoc($result);
            if($row["cantidad"] == 1){ // Si la cantidad es 1 se borra la entrada de la tabla (no queremos guardar cantidad 0, no tiene sentido)
                
                if($conn->query($sqlDelete) === TRUE){ // Si existe actualiza el valor cantidad en +1
                    $j=$j+1; // avanza al siguiente id
                }
                else{ //control errores
                    echo "Error al eliminar la carta" . $conn->error;
                    $j=$j+1; // avanza al siguiente id
                }
            }

            else { // Si la carta tiene mas de 1 copia
                
                if ($conn->query($sqlUpdate) === TRUE) { // Reduce la cantidad en 1
                    $j=$j+1; //avanza al siguiente id
                } else { // Control de errores
                    echo "Error al ejecutar la query: " . $conn->error;
                    $j=$j+1; //avanza al siguiente id
                }
            }   
        }        
    }
}

function sellSelectedCards($conn, $auxArray, $uid){
    $j=0;
    $tamañoArrayAux = count($auxArray);
    while($j < $tamañoArrayAux){
        // ID actual de la carta
        $idCarta = $auxArray[$j];
        // Consultas SQL
        $sqlCheckVenta = "SELECT p.idCarta, p.cantidad 
        FROM puesta_en p JOIN ventas v ON (p.idVenta = v.idVenta)
        WHERE idCarta='$idCarta' AND vendedor = $uid";
        //$sqlGetLastVenta = "SELECT MAX(idVenta) AS max_id FROM ventas WHERE vendedor = $uid";
        $sqlCreateVenta = "INSERT INTO ventas (idVenta, direccion, cantComprada, precioTotal, estado, emailComprador, comprador, vendedor) VALUES (null, null, null, null, null, null, null, $uid)";
        $sqlInsertVenta = "INSERT INTO puesta_en (idCarta, idVenta, cantidad) SELECT $idCarta, idVenta, 1 FROM ventas WHERE vendedor = $uid AND idVenta = (SELECT MAX(idVenta) AS max_id FROM ventas WHERE vendedor = $uid)";
        $sqlUpdateVenta = "UPDATE puesta_en SET cantidad = (cantidad + 1) 
        WHERE idCarta = '$idCarta' AND idVenta = 
            (SELECT v.idVenta from ventas v JOIN puesta_en p ON (v.idVenta = p.idVenta)
            where vendedor = $uid and idCarta = $idCarta)";
        
	// COMPROBAR SI EL USUARIO TIENE MAS VENTAS

        // Comprueba si la carta existe en la tabla puesta_en
        $result = $conn->query($sqlCheckVenta);

        if (mysqli_num_rows($result)>0){ 
            if ($conn->query($sqlUpdateVenta) === TRUE){ // Si existe actualiza el valor cantidad en +1
                $j=$j+1; // avanza al siguiente id
            }
            else{ //control errores
                echo "Error al actualizar la cantidad de la carta" . $conn->error;
                $j=$j+1; // avanza al siguiente id
            }
        }

        else { // Si la carta no existe en la tabla puesta_en
            // Primero hay que crear la venta en la tabla ventas

            if($conn->query($sqlCreateVenta) === TRUE){
                
                //Inserta la carta en la BD con valor de cantidad = 1
                if ($conn->query($sqlInsertVenta) === TRUE) { 
                    $j=$j+1; //avanza al siguiente id
                } else { // Control de errores
                    echo "Error al ejecutar la query: " . $conn->error;
                    $j=$j+1; //avanza al siguiente id
                }
            }

            else {
                echo "Error al crear venta en tabla ventas. Error: " . $conn->error;
                $j=$j+1; //avanza al siguiente id
            }
        }

        
    }
}

function buySelectedCards($conn, $cantidades, $idVentas, $idComprador, $direccion, $email){
    $total = 0;

    foreach($cantidades as $idCarta => $cantidad){
        $cantidad = intval($cantidad);

        if($cantidad > 0){
            $idVenta = intval($idVentas[$idCarta]);

            // Obtener precio de la carta
            // Método mas seguro de querys para MYSQL anti inyección
            $proc = $conn->prepare("SELECT precio_eur FROM cartas WHERE idCarta = ?");
            $proc->bind_param("i", $idCarta);
            $proc->execute();
            $proc->bind_result($precio);

            if($proc->fetch()){
                $total += $precio * $cantidad;
            }
            $proc->close();

            // Obtener cantidad actual
            $proc = $conn->prepare("SELECT cantidad FROM puesta_en WHERE idVenta = ? AND idCarta = ?");
            $proc->bind_param("ii", $idVenta, $idCarta);
            $proc->execute();
            $proc->store_result();  // ← IMPORTANTE
            $proc->bind_result($cantidadActual);
            
            if ($proc->fetch()) {
                $nuevaCantidad = max(0, $cantidadActual - $cantidad);
            }
                    
                // Actualizar cantidad en puesta_en
                // No queremos borrar las entradas con cantidad = 0 
                // Ya que en el futuro nos puede interesar hacer un volcado
                // De toda esa información, para hacer reportes o informes o lo que sea
                if($nuevaCantidad >= 0){ 
                    
                    //Modificar tabla
                    $procUpdateP = $conn->prepare("UPDATE puesta_en SET cantidad = ? WHERE idVenta = ? AND idCarta = ?");
                    $procUpdateP->bind_param("iii", $nuevaCantidad, $idVenta, $idCarta);
                    $procUpdateP->execute();
                    $procUpdateP->close();
                    
                    // En esta cantidad se indica cuantas han sido compradas
                    $procUpdateV = $conn->prepare("UPDATE ventas SET precioTotal = ?, cantComprada = ?, direccion = ?, emailComprador = ?, estado = 'p', comprador = ? WHERE idVenta = ?");
                    $procUpdateV->bind_param("dissii", $total, $cantidad, $direccion, $email, $idComprador, $idVenta);
                    $procUpdateV->execute();
                    $procUpdateV->close();
               
                }
               
                
                
            
            $proc->close();
        }
    }

    return $total;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {   

    // Conectar a BD
    $conn = conectar();

    // Obtener cartas seleccionadas
    $auxArray = getSelectedCards();
    
    // Obtener uid para seleccionar el inventario
    $uid = $_SESSION["uid"];

    // Obtener accion insert/delete/sell/buy
    if (isset($_POST['metodo'])) {

        $metodo = $_POST['metodo']; 
        // No es necesario hacer unset($_POST["variable"])
    } else {
        
        $metodo = $_SESSION["metodo"]; 
         // Para evitar problemas, la info ya esta almacenada y podemos vaciar 
         // la variable de sesion
        unset($_SESSION["metodo"]);
    }

    if($metodo == "insert"){
        // $_POST['cantidad'] es un array asociativo idCarta => cantidad
        $cantidades = $_POST['cantidad'] ?? [];
        // Guardar cartas en inventario
        saveSelectedCards($conn, $cantidades, $uid);
    } 
    elseif($metodo == "delete"){

        // Quitar cartas del inventario
        deleteSelectedCards($conn, $auxArray, $uid);
    }
    elseif($metodo == "sell"){

        // Insertar cartas en ventas
        sellSelectedCards($conn, $auxArray, $uid);
        // Borrar cartas del inventario
        deleteSelectedCards($conn, $auxArray, $uid);
    }
    elseif($metodo == "buy"){

        // NOTAS: =======================================================================
        // Aqui es donde encontramos el problema del mostrador de ventas
        // No puedo eliminar las cartas de ventas porque la información es necesaria
        // Pero si la dejo seguirá apareciendo como venta aunque ya se haya comprado

        // Lo solucionamos comprobando el valor de la columna comprador en la tabla ventas
        // Si es NULL, es una venta activa, si tiene un valor es una venta cerrada
        // Para hacer el registro solo tenemos que comprobar las ventas con idVendedor != null

        // Sigue existiendo un problema con este metodo y es que si no se compra la cantidad
        // total de cartas que vende un vendedor, el usuario que compre el sobrante reescribirá
        // la entrada con sus datos, perdiendo así información de la/s compra/s previas
        
        // Es por esto que debemos crear un par de tablas extra como mostrador y registroVentas 

        //================================================================================

        // Recuperar cantidad para despues
        // $_POST['cantidad'] es un array asociativo idCarta => cantidad

        //Despues hay que hacer unset de las variables
        if(!isset($_SESSION["cantidades"]) && !isset($_SESSION["idVentas"])){
            $_SESSION["cantidades"] = $_POST['cantidad'] ?? [];
            // Esta lo mismo pero con idVenta
            $_SESSION["idVentas"] = $_POST['idVenta'] ?? [];
        }

        // Utilizamos puerta logica || en caso de que uno de los campos esté vacio que no entre
        if(!isset($_POST["email"]) || !isset($_POST["direccion"])){
            // menu para que el comprador ponga su info
            // tenemos que asegurarnos de volver a entrar en la opción buy
            header("Location: ./getBuyerData.php");        
            exit;

        } else {
            // Ya que los mandamos de forma segura hay que decodearlos
            $direccion = htmlspecialchars_decode($_POST["direccion"]);
            $email = htmlspecialchars_decode($_POST["email"]);
            // Aqui ya si actualizamos la BD
            buySelectedCards($conn, $_SESSION["cantidades"], $_SESSION["idVentas"], $uid, $direccion, $email);
            
            // Vaciar todas las variables de sesion utilizadas
            // para que no generen conflictos en el futuro
            unset($_SESSION["cantidades"]);
            unset($_SESSION["idVentas"]);
            unset($_SESSION["buyerData"]);
        }        

    }
    else{
        echo "Error al seleccionar metodo";
    }
    

    $conn->close();

    // Redirigir a mis_cartas
    header("Location: index.php");

}  

?>
