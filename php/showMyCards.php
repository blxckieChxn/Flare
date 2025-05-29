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

   
    <h1><?php echo $_SESSION["username"] . "'s "?>Cards</h1>
    <hr>
<?php
    include_once "conexion.php";


    function showMyCards($conn, $abreviatura){

        $username = $_SESSION["username"];  
      
        $queryByEdition = "SELECT c.imagen, c.idCarta, c.precio_eur, c.precio_usd, f.cantidad 
        FROM usuarios u JOIN inventarios i ON (u.uid = i.uid) JOIN formado_por f ON 
        (i.idInventario = f.idInventario) JOIN cartas c ON (f.idCarta = c.idCarta)
        JOIN ediciones e ON (c.idEdicion = e.idEdicion)
        WHERE e.abreviatura = '$abreviatura' AND u.nombre = '$username' ORDER BY c.numCarta";
        
        $queryAll = "SELECT c.imagen, c.idCarta, c.precio_eur, c.precio_usd, f.cantidad 
        FROM usuarios u JOIN inventarios i ON (u.uid = i.uid) JOIN formado_por f ON 
        (i.idInventario = f.idInventario) JOIN cartas c ON (f.idCarta = c.idCarta)
        JOIN ediciones e ON (c.idEdicion = e.idEdicion)
        WHERE u.nombre = '$username'
        ORDER BY e.idEdicion";
        
        if($abreviatura == 'null'){
          $result = $conn->query($queryAll);
        } else {
          $result = $conn->query($queryByEdition);
        }
        
        
        if (mysqli_num_rows($result) > 0){
            echo "<form action='./obtenerImagenSelect.php' method='post'>";
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
              echo "<td>" . $row["precio_eur"] . "€ | " . $row["precio_usd"] . "$ <div class='card'><img src='http://" . $_SERVER['SERVER_ADDR'] . "/" . $imagenSegura . "' width='280' height='410' loading='eager' class='card' data-value='" . $row["idCarta"] . "' data-selected='false' style='border-radius: 10px;'></div></td>";
                            $count = $count + 1;
              $i=$i+1;
              
            }
        } else {
          echo "<h3>Aun no tienes cartas en el inventario, prueba a añadir algunas</h3>";
        }
        echo "</table>";   
        echo "<input type='hidden' id='selectedImages' name='selectedImages' value='[]'>";
        echo "<div class='botones-container'>";
        echo "<button type='submit' class='botonDelete' name='metodo' value='delete'>Eliminar Cartas</button>";
        echo "<button type='submit' class='botonSell' name='metodo' value='sell'>Vender Cartas</button>";
        echo "</div>";
        echo "</form>";   
        echo "<script src='../js/selectCards.js'></script>";
        

        return NULL;
    }
        
    if($_SESSION["edicion_busqueda"]){
      // Establecer método (delete)
      $_SESSION["metodo"] = "delete";

      // Recuperar edicion de busqueda
      $abreviatura = $_SESSION["edicion_busqueda"];
      $conn = conectar();
      showMyCards($conn, $abreviatura);
      $conn->close();
    }
    
?>

</body>