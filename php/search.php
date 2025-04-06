<?php

    session_start();
    //INCLUDE PARA CONECTAR A LA BD
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //activar la flag de busqueda
    $_SESSION['flag'] = 1;

    // Recuperar los valores del formulario
    $nombre = $_POST["name"];
    $edicion = $_POST["edicion"];


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

    // Consulta SQL para buscar una carta COLLATE (no discrimina mayusculas de minusculas) SOUNDEX (busquedas con nombres similares)
    $sql = "SELECT * FROM cartas 
        WHERE nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%' 
        AND SOUNDEX(nombre) = SOUNDEX('$nombre') 
        AND edicion = '$edicion'";  
    $result = $conn->query($sql);

    // Imprimir datos de cada fila
    while($row = $result->fetch_assoc()) {
        $_SESSION["nombre"] = $row["nombre"];
        $_SESSION["numCarta"] = $row["num_carta"];
        $_SESSION["edicion"]= $row["edicion"];
        $_SESSION["precioEUR"] = $row["precio_eur"];
        $_SESSION["precioUSD"] = $row["precio_usd"];
        $_SESSION["imagen"]= $row["imagen"];
    }

    // Cerrar conexión
    $conn->close();
    header("Location: index.php");
} 
?>