<?php
function conectar(){
    // Conexión a la base de datos
    $servername = "localhost";
    $db_username = "flare"; // Usuario de la base de datos
    $db_password = "sdfsdf"; // Contraseña de la base de datos
    $dbname = "flare";

    // Crear conexión
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error en la conexión: " . $conn->connect_error);
    }
    return $conn;
}
?>