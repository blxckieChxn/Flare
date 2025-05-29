<?php
  session_start();

  if(!isset( $_SESSION['username'])){
    echo '
      <script>
        alert("Primero debes iniciar sesion");
        window.location = "../index.html"
      </script>
    ';
    session_destroy();
    die();
  }
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="../css/opciones.css">
  <title>Home</title>
  <link rel="icon" href="../img/mtgLogo.ico" type="image/x-icon">
  <link rel="shortcut icon" href="../img/mtgLogo.ico" type="image/x-icon">
</head>

<body>
<div class="main">  
  <div class="sidebar">  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="ediciones.php">Editions</a></li>
    <li><a href="my_cards.php">My cards</a></li>
    <li><a href="forSale.php">For Sale</a></li>
    <li><a href="pedidos.php">My orders</a></li>
    <li><a href="decks.php">Decks</a></li>
    <li><a href="opciones.php">Options</a></li>
    <form action=""><li><a href="cerrarSesion.php">Log out</a></li></form>
  </ul></div>

  <div class="contenido">
    <div class="imgBackground">
      <div class="img">
        <img src="../img/mtgLogo.png" height="15%" width="15%">
        <br>
      </div>
    </div>

    <hr>

    <h1>Options</h1>
    <hr>

    <form action="" method="post">
	    <div class="botones-container">
        	<button class='id_button' type='submit'>Solicitar ID</button>
	    </div>
    </form>
    
    <?php
    include_once "conexion.php";

    session_start(); // Asegúrate de iniciar la sesión antes de acceder a $_SESSION

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        // Conexión a la base de datos
        $servername = "localhost";
        $db_username = "flare";
        $db_password = "sdfsdf";
        $dbname = "flare";

        // Crear conexión
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);

        // Verificar conexión
        if($conn->connect_error){
            die("Conexión fallida: " . $conn->connect_error);
        }

        // Obtener el nombre del usuario desde la sesión
        if(isset($_SESSION["username"])){
            $username = $_SESSION["username"];

            // Consulta segura usando prepared statements para evitar inyección SQL
            $stmt = $conn->prepare("SELECT uid FROM usuarios WHERE nombre = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->bind_result($uid);

            if ($stmt->fetch()) {
                echo "<h3 class='id'><b>Tu ID de Flare es: </b>" . htmlspecialchars($uid) . "</h3>";
            } else {
                echo "<h3><b>[!] Usuario no encontrado.</b></h3>";
            }

            $stmt->close();
        } else {
            echo "<h3><b>[!] No hay sesión iniciada.</b></h3>";
        }

        $conn->close();
    }
?>

   
  </div>
</div>  
</body>
</html>
