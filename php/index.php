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
  <link rel="stylesheet" href="../css/index.css">
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

    <h1>Search</h1>
    <hr>
    
    <form action="" method="post">
      <table border="1">
          <tr>
              <th><input type="text" name="name" id="name" placeholder="Name" required></th>
              <th><?php
                    include_once "conexion.php";

                    $conn = conectar();
                    $sql = "SELECT nombre, abreviatura FROM ediciones";
                    $result = $conn->query($sql);
                    
                    if (mysqli_num_rows($result) > 0) {
                      echo "<select name='abreviatura' id='abreviatura'>";
                      echo "<option value='null'>Edition</option>";
                      
                      while($row = mysqli_fetch_assoc($result)){
                        echo "<option value=" . $row["abreviatura"] . ">" . $row["nombre"] . "</option>";
                      }
                    
                      echo "</select>";
                    }
                    $conn->close();
                  ?></th>
              <th><input type="submit" value="Search"></th>
          </tr>
      </table> 
    </form>
    <?php 
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Recuperar el valor del formulario                  
          $_SESSION["edicion_busqueda_index"] = $_POST["abreviatura"];
          $_SESSION["nombreCarta"] = $_POST["name"];
          header("Location: showSearch.php");
          exit;
        }
    ?>
  </div>
</div>  
</body>
</html>