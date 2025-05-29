<?php
  include_once "conexion.php";
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
  <link rel="stylesheet" href="../css/ediciones.css">
  <title>Editions</title>
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

    <h1>Editions</h1>
    <hr>

    <form action="" method="post">
    <input type="hidden" name="abreviatura" id="inputAbreviatura">

    <table border='1'id="tabla-ediciones">
      <?php
        include_once "conexion.php";
        function getEditions(){
          $conn=conectar();
          $query = "SELECT nombre, abreviatura FROM ediciones";
          $result = $conn->query($query);
  
          if (mysqli_num_rows($result) > 0) {
              echo "<form action='' method='post'>";
  
              while($row = mysqli_fetch_assoc($result)){
                  echo "<tr class='fila-edicion' data-abreviatura='" . $row['abreviatura'] . "'>";
                  echo "<td>" . $row['nombre'] . "</td>";
                  echo "</tr>";
              }
  
              echo "</form>";
          }
  
          return NULL;
      }
        getEditions();
        
        
      ?>
    </table>

  <br>
  <button class="sendButton" type="submit">Enviar selección</button><br>
</form>
      <?php
        if($_SERVER["REQUEST_METHOD"] == "POST"){
          $_SESSION["edicion_busqueda"] = $_POST["abreviatura"];
          header("location: showEdition.php");
        }
      ?>
  </div>
  <script src="../js/selectRow.js"></script>
</body>
</html>