<?php
session_start();
include_once "conexion.php";


function posibleEdicion($palabra){
    return (strlen($palabra) === 3) && ($palabra === strtoupper($palabra));
}

function compararEdiciones($pe){
    $conn = conectar();
    $query = "SELECT abreviatura FROM ediciones WHERE abreviatura = '$pe'";
    $result = $conn->query($query);

    if(mysqli_num_rows($result) > 0){
        return true;
    } else {
        return false;
    }
}

function extraerEdiciones(&$palabras){
    $posiblesEdiciones = [];
    $edicionesExtraidas = [];

    foreach ($palabras as $p) {
        if (posibleEdicion($p) === true) {
            
            $posiblesEdiciones[] = $p;
        }
    }

    foreach ($posiblesEdiciones as $pe){
        if(compararEdiciones($pe) == true){
            $edicionesExtraidas[] = $pe;
        }
    }

    // Quitar las posibles ediciones ya extraidas del resto del palabras
    // Porque ya no las necesitamos
    $palabras = array_diff($palabras, $posiblesEdiciones);
    // Reindexar el array
    $palabras = array_values($palabras);

    $edicionesExtraidas = array_unique($edicionesExtraidas);

    return $edicionesExtraidas;
}

function extraerNombres($data){
    $resultado = [];
    $longitud = count($data);

    for ($i = 0; $i < $longitud; $i++) {
        if (preg_match('/^[A-Z]/', $data[$i])) {
            $palabra1 = $data[$i];
            $palabra2 = ($i + 1 < $longitud) ? $data[$i + 1] : null;

            if ($palabra2 !== null) {
                $resultado[] = $palabra1 . " " . $palabra2;
            } else {
                $resultado[] = $palabra1;
            }
        }
    }

    return $resultado;
}

function compararBD($nombres, $edicionesExtraidas){
    
    $resultados = [];

    if(!empty($edicionesExtraidas)){
        foreach($ediciones as $ed){
            foreach($nombres as $nombre){
                $conn = conectar();
                $sqlByEdition = "SELECT idCarta
                                FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
                                WHERE c.nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%' AND e.abreviatura = '$ed'";
                $result = $conn->query($sqlByEdition);
                if(mysqli_num_rows($result) > 0){
                    while($row = $result->fetch_assoc()){
                        array_push($resultados, $row["idCarta"]);
                    }
                }
                $conn->close();
            }
        }
    } else {
        foreach($nombres as $nombre){
            $conn = conectar();
            $sqlAll = "SELECT idCarta 
                        FROM cartas 
                        WHERE nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%'";
            $result = $conn->query($sqlAll);
            if(mysqli_num_rows($result) > 0){
                while($row = $result->fetch_assoc()){
                    array_push($resultados, $row["idCarta"]);
                }
            }
            $conn->close();
        }
    }    
    return $resultados;
}

function buscarIDs($nombre) {
    $conn = conectar();

    // Escapar el string para seguridad
    $nombre = $conn->real_escape_string($nombre);

    $sql = "SELECT idCarta FROM cartas WHERE nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%'";
    $result = $conn->query($sql);

    $ids = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['idCarta'];
        }
    }

    $conn->close();
    return $ids;
}

function filtradoPorRepeticion($nombres, $edicionesExtraidas) {
    $total = count($nombres);
    $resultadosFinales = [];
    $idsYaAgregados = [];  // Para evitar repetir IDs globalmente

    for ($i = 0; $i < $total; $i++) {
        $mejorMatch = null;
        $mejorFrase = "";
        $menorCantidad = PHP_INT_MAX;

        // 1 palabra
        $frase1 = $nombres[$i];
        $arrayID1 = buscarIDs($frase1);

        if (!empty($arrayID1) && count($arrayID1) < $menorCantidad) {
            $mejorMatch = $arrayID1;
            $mejorFrase = $frase1;
            $menorCantidad = count($arrayID1);
        }

        // 2 palabras
        if ($i + 1 < $total) {
            $frase2 = $frase1 . ' ' . $nombres[$i + 1];
            $arrayID2 = buscarIDs($frase2);
            $interseccion = (!empty($arrayID1) && !empty($arrayID2)) ? array_intersect($arrayID1, $arrayID2) : [];
            $interseccion = array_values($interseccion);

            if (!empty($interseccion) && count($interseccion) < $menorCantidad) {
                $mejorMatch = $interseccion;
                $mejorFrase = $frase2;
                $menorCantidad = count($interseccion);
            }

            // 3 palabras
            if ($i + 2 < $total) {
                $frase3 = $frase2 . ' ' . $nombres[$i + 2];
                $arrayID3 = buscarIDs($frase3);
                $interseccion3 = (!empty($interseccion) && !empty($arrayID3)) ? array_intersect($interseccion, $arrayID3) : [];
                $interseccion3 = array_values($interseccion3);

                if (!empty($interseccion3) && count($interseccion3) < $menorCantidad) {
                    $mejorMatch = $interseccion3;
                    $mejorFrase = $frase3;
                    $menorCantidad = count($interseccion3);
                }
            }
        }

        // Guardar si hay 5 o menos resultados y aún no están en los acumulados
        if (!empty($mejorMatch) && $menorCantidad <= 5) {
            $mejorMatch = array_unique($mejorMatch);
            $mejorMatch = array_diff($mejorMatch, $idsYaAgregados);  // Quitar los ya usados
            $mejorMatch = array_values($mejorMatch);  // Reindexar

            if (!empty($mejorMatch)) {
                $resultadosFinales[] = [
                    'frase' => $mejorFrase,
                    'ids' => $mejorMatch
                ];

                // Añadir estos IDs al listado global para no repetir
                $idsYaAgregados = array_merge($idsYaAgregados, $mejorMatch);
            }
        }
    }

    return $resultadosFinales;
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    
    // Obtener el valor de la checkbox
    $foil = isset($_POST['foil']) ? true : false;
    
    // Validar que no haya error en la carga del archivo
    if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        echo "Error al subir la imagen.";
        exit;
    }

    // Detecta la imagen 
    $imagenTmp = $_FILES['imagen']['tmp_name'];
    $mime = mime_content_type($imagenTmp);
    $nombreOriginal = $_FILES['imagen']['name'];

    // Inicializar cURL
    $cfile = new CURLFile($imagenTmp, $mime, $nombreOriginal);

    $curl = curl_init();

    // Parametros de cURL
    curl_setopt_array($curl, [
        CURLOPT_URL => 'http://localhost:5001/ocr',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['imagen' => $cfile],
        // Han necesitado ser aumentados por el tamaño de algunas imagenes
        // Necesitaba más tiempo para procesarlas
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 60
    ]);

    // Enviar imagen a localhost:5001
    $respuesta = curl_exec($curl);

    // Manejar error de conexión
    if (curl_errno($curl)) {
        echo "Error de conexión: " . curl_error($curl);
        curl_close($curl);
        exit;
    }

    // Se cierra sesion en cURL
    curl_close($curl);

    // Intentar decodificar la respuesta
    $datos = json_decode($respuesta, true);
    $posibleEdicion = [];
    if (isset($datos['texto'])) {
        echo "<h3>Texto extraído:</h3>";
        $data = htmlspecialchars($datos['texto']);
        //echo "$data";
        //echo "$data";
        // Separar por palabras
        $palabras = preg_split('/\s+/', $data);
        
        $posiblesEdiciones = [];  
        $edicionesExtraidas = [];
        $idCartas = [];
        
        // Se extraen las palabras con 3 caracteres
        // Se cambian todos a MAYUSC
        // Se comparan las abreviaturas de la BD
        // Se dejan solo las coincidencias
        // No siempre reconoce la edición
        $edicionesExtraidas = extraerEdiciones($palabras);
        print_r($edicionesExtraidas);
        //print_r($palabras);

        // Interpretamos el resto de palabras como posibilidades de nombre de cartas
        // Como las cabeceras es lo primero que lee el programa, los nombres estarán 
        // Al principio del array (si se colocan en linea en la imagen original)
        $nombres = $palabras; // que cutre eres capullo
        $nombres = array_slice($nombres, 0, 10);
        print_r($nombres);
        $idCartas = filtradoPorRepeticion($nombres, $edicionesExtraidas);
        //$idCartas = compararBD($nombres, $edicionesExtraidas);
        print_r($idCartas);
        $_SESSION["OCR"] = $idCartas;
        header("Location: showOCR.php");
        exit;

    } elseif (isset($datos['error'])) {
        echo "Error al extraer texto: " . htmlspecialchars($datos['error']);
    } else {
        echo "Respuesta inesperada del servidor OCR.";
    }
}
?>
