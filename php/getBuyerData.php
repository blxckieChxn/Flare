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
  <link rel="stylesheet" href="../css/getBuyerData.css">
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

    <h1>User Data for Order</h1>
    <hr>

      <form action="obtenerImagenSelect.php" method="post">
        <table id="tabla-data">
      
          <label for="direccion">Dirección de entrega:</label><br>
          <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($direccion); ?>" required><br><br>

          <label for="email">Email:</label><br>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br><br>

          <button type='submit' class='botonSave' name='metodo' value="buy">Comprar Cartas</button>
      </table>
    </form>
      

    <br>
    <br>
  </form>
  </div>
</body>
</html>