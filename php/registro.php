<?php

session_start();
// Conexión a la base de datos
$servername = "localhost";
$username = "root"; // Nombre de usuario de la base de datos
$password = ""; // Contraseña de la base de datos
$dbname = "mtg";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

//obtener valores de los campos del registro
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user=$_POST["username"];
    $passw=$_POST["password"];
    // Ejecutar la query
    $sql = "INSERT INTO usuarios (uid, nombre, contraseña, fecha_alta) VALUES ('', '$user', SHA2('$passw', 256), default)";

    if ($conn->query($sql) === TRUE) {
        echo "Bienvenid@ al inventario de cartas, ya puedes iniciar sesión =)";

    } else {
        echo "Error al ejecutar la query: " . $conn->error;
    }
}

// Cerrar conexión
$conn->close();
?>