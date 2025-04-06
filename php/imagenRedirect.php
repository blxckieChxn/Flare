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

  header('Content-Type: image/jpg');
  
  echo $_SESSION["imagen"];
  header("Location: index.php");

?>
