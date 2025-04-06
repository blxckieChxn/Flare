<?php
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
}

?>