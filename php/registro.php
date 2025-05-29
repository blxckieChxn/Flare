<?php
session_start();
// Conexión a la base de datos
$servername = "localhost";
$username = "flare"; // Nombre de usuario de la base de datos
$password = "sdfsdf"; // Contraseña de la base de datos
$dbname = "flare";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

//obtener valores de los campos del registro
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user = $_POST["username"];
    $passw = $_POST["password"];
    $pass2 = $_POST["password2"];
    
    if($passw == $passw2){
        // Ejecutar la query
        $sql = "INSERT INTO usuarios (uid, nombre, password, fecha_alta) VALUES (null, '$user', SHA2('$passw', 256), default)";
        
        if ($conn->query($sql) === TRUE) {
            echo "Bienvenid@ al inventario de cartas, ya puedes iniciar sesión =)";
    
        } else {
            echo "Error al ejecutar la query: " . $conn->error;
        }
    } else {
        echo '
      <script>
        alert("Las contraseñas no coinciden");
        window.location = "../html/registro.html"
      </script>
    ';
    }
    
}

// Cerrar conexión
$conn->close();
?>