<?php

session_start();
include_once "conexion.php";

function selectQuery($abreviatura, $nombre){
    $queryByEdition = "SELECT c.nombre, c.numCarta, e.abreviatura, c.precio_eur, c.precio_usd, c.imagen 
                        FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
                        WHERE c.nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%' 
                        AND e.abreviatura = '$abreviatura'";  
    
    $queryAll = "SELECT c.nombre, c.numCarta, e.abreviatura, c.precio_eur, c.precio_usd, c.imagen 
        FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
        WHERE c.nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%'";  
    
    if($abreviatura == 'null'){
        $result = $queryAll;
    } else {
        $result = $queryByEdition;
    }

    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Flag de busqueda
    $_SESSION["flag"]=1;

    // Recuperar los valores del formulario
    $nombre = $_POST["name"];
    $abreviatura = $_POST["abreviatura"];

    $conn = conectar();

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error en la conexión: " . $conn->connect_error);
    }

    // Consulta SQL para buscar una carta COLLATE (no discrimina mayusculas de minusculas) SOUNDEX (busquedas con nombres similares)
    $sql = selectQuery($abreviatura, $nombre);  
    $result = $conn->query($sql);

    // Recuperar datos de cada fila
    while($row = $result->fetch_assoc()) {
        $_SESSION["nombre"] = $row["nombre"];
        $_SESSION["numCarta"] = $row["numCarta"];
        $_SESSION["abreviatura"]= $row["abreviatura"];
        $_SESSION["precioEUR"] = $row["precio_eur"];
        $_SESSION["precioUSD"] = $row["precio_usd"];
        $_SESSION["imagen"]= $row["imagen"];
    }

    // Cerrar conexión
    $conn->close();
    header("Location: index.php");
} 
?>