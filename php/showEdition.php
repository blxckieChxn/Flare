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
  <link rel="stylesheet" href="../css/allEditions.css">
  <title>Cartas Edicion</title>
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
<?php
    include_once "conexion.php";


    function showEdition($conn, $abreviatura){
        $query = "SELECT c.imagen, c.idCarta, c.precio_eur, c.precio_usd
        FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
        WHERE e.abreviatura = '$abreviatura' ORDER BY c.numCarta";
        $result = $conn->query($query);
        
        if (mysqli_num_rows($result) > 0){
            echo "<table border='1'>";
            $count=0;
            $i=0;
            echo "<tr>";
            while($row = mysqli_fetch_assoc($result)) {
              if($count == 4){
                echo "</tr>";
                echo "<tr>";
                $count=0;
              }
              // El id del div html es igual que el id en la base de datos, para poder recuperarlo con onclick()
              $imagenSegura = htmlspecialchars($row["imagen"], ENT_QUOTES, 'UTF-8');
              echo "<td class='containerCarta'>" . $row["precio_eur"] . "€ | " . $row["precio_usd"] . "$ <div><img src='http://" . $_SERVER['SERVER_ADDR'] . "/" . $imagenSegura . "' width='280' height='410' loading='eager' data-value='" . $row["idCarta"] . "' data-selected='false' style='border-radius: 10px;'></div></td>";
                            $count = $count + 1;
              $i=$i+1;
              
            }
        }
        echo "</table>";
        
        return NULL;
    }
        
    if($_SESSION["edicion_busqueda"]){
      // Establecer método (insert)
      $_SESSION["metodo"] = "insert";

      // Recuperar edicion de busqueda
      $abreviatura = $_SESSION["edicion_busqueda"];
      $conn = conectar();
      showEdition($conn, $abreviatura);
      $conn->close();
    }
    
?>

</body>