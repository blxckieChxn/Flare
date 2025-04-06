<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Conexión a la base de datos
    $servername = "localhost";
    $db_username = "root"; // Usuario de la base de datos
    $db_password = ""; // Contraseña de la base de datos
    $dbname = "mtg";

    // Crear conexión
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error en la conexión: " . $conn->connect_error);
    }

    // Verificar si el campo selectedImages está presente en $_POST
    if (isset($_POST['selectedImages'])) {
        // Obtener el valor de las imágenes seleccionadas
        $selectedImages = json_decode($_POST['selectedImages'], true);
        
        if (is_array($selectedImages) && !empty($selectedImages)) {
            $tamañoArray = count($selectedImages); // Por algun motivo añade celdas vacias 
            //echo "Has seleccionado: " . implode(', ', array_map('htmlspecialchars', $selectedImages));
            // Eliminar celdas vacías del array
            $auxArray = [];
            $i=0;
            $p=0;
            while($i < $tamañoArray){
                if($selectedImages[$i] != null && is_numeric($selectedImages[$i])){
                    $auxArray[$p] = $selectedImages[$i];
                    $i=$i+1;
                    $p=$p+1;
                } else {
                    $i=$i+1;
                }
            }

            $selectedImages = sort($selectedImages);
            
            echo "Has seleccionado: " . implode(', ', array_map('htmlspecialchars', $auxArray));

            //Almacenar las cartas en la base de datos del inventario
            $j=0;
            $tamañoArrayAux = count($auxArray);
            $almacenadas =0; // Querys ejecutadas con exito

            while($j < $tamañoArrayAux){
                $id = $auxArray[$j];
                $sqlInsert = "INSERT INTO my_cards (id, nombre, numCarta, edicion, imagen, precio_eur, precio_usd, categoria, last_update, cantidad) Select id, nombre, num_carta, edicion, imagen, precio_eur, precio_usd, categoria, last_update, 1 from cartas where id='$id'";
                $sqlCheck = "SELECT id FROM my_cards WHERE id='$id'";
                $sqlUpdate = "UPDATE my_cards SET cantidad = (cantidad + 1) WHERE id= '$id'";
                $result = $conn->query($sqlCheck);
                if (mysqli_num_rows($result)>0){ // Comprueba si la carta existe en la BD
                    if ($conn->query($sqlUpdate) === TRUE){ // Si existe actualiza el valor cantidad en +1
                        $j=$j+1; // avanza al siguiente id
                    }
                    else{ //control errores
                        echo "Error al actualizar la cantidad de la carta" . $conn->error;
                        $j=$j+1; // avanza al siguiente id
                    }
                }

                else { // Si la carta no existe en la BD
                    if ($conn->query($sqlInsert) === TRUE) { //Inserta la carta en la BD con valor de cantidad = 1
                        $j=$j+1; //avanza al siguiente id
                    } else { // Control de errores
                        echo "Error al ejecutar la query: " . $conn->error;
                        $j=$j+1; //avanza al siguiente id
                    }
                }
            }
            //header("Location: my_cards.php");

        } else {
            echo "No se ha seleccionado ninguna imagen 1.";
        }
    } else {
        echo "No se ha seleccionado ninguna imagen 2.";
        
    }
}
?>