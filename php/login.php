<?php
    session_start();
// Verificar si se enviaron datos de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar los valores del formulario
    $username = $_POST["username"];
    $password = $_POST["password"];

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

    // Consulta SQL para verificar las credenciales de inicio de sesión
    $sql = "SELECT * FROM usuarios WHERE nombre = '$username' AND password = SHA2('$password', 256);";
    $result = $conn->query($sql);

    // Verificar si se encontraron resultados
    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        // Credenciales válidas, inicio de sesión exitoso
        
        $_SESSION['username'] = $username;
        $_SESSION['uid'] = $row["uid"];
        $_SESSION['flag'] = 0;
        header("Location: index.php");

    } else {
        // Credenciales inválidas, inicio de sesión fallido
        echo "Nombre de usuario o contraseña incorrectos.";
    }

    // Cerrar conexión
    $conn->close();
}
?>