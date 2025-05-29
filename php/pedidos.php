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
<!--CLASICA WISHLIST O CARRITO O COMO QUIERAS LLAMARLO-->
<!--SE IMPLEMENTA CON COOKIES DE SESION ME IMAGINO NO ME SEAS CAFRE CON LA BD-->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Opciones</title>
        <link rel="stylesheet" type="text/css" href="../css/pedidos.css">
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
                <img src="../img/mtgLogo.png" height="20%" width="25%">
                <br>
            </div>
          </div>
          <hr>
          <h1>My orders</h1>
          <hr>

          <form action="" method="post">
	   
          </form>
        </div>

    </div>
</body>
</html>