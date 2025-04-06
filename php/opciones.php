<?php
  session_start();

  if(!isset( $_SESSION['username'])){
    echo '
      <script>
        alert("Primero debes iniciar sesion");
        window.location = "login.html"
      </script>
    ';
    session_destroy();
    die();
  }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Opciones</title>
        <link rel="stylesheet" type="text/css" href="opciones.css">
    </head>
    <body>
    <div class="main">  
        <div class="sidebar">  <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="forSale.php">For Sale</a></li>
          <li><a href="wanted.php">Wanted</a></li>
          <li><a href="decks.php">Decks</a></li>
          <li><a href="opciones.php">Options</a></li>
          <form action=""><li><a href="cerrarSesion.php">Log out</a></li></form>
        </ul></div>
      
        <div class="contenido">
          <div class="imgBackground">
            <div class="img">
                <img src="mtgLogo.png" height="20%" width="25%">
                <br>
            </div>
          </div>
        </div>
          <hr>
    </div>
</body>
</html>