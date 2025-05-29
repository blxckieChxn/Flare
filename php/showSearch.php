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
  <title>Search</title>
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

   
    <h1>Searching for: "<?php echo $_SESSION["nombreCarta"]?>"</h1>
    <hr>
<?php
    include_once "conexion.php";


    function showSearch($conn, $abreviatura, $nombre){
        
        $queryByEdition = "SELECT c.idCarta, c.nombre, c.numCarta, e.abreviatura, c.precio_eur, c.precio_usd, c.imagen 
                        FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
                        WHERE c.nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%' 
                        AND e.abreviatura = '$abreviatura'";  
    
        $queryAll = "SELECT c.idCarta, c.nombre, c.numCarta, e.abreviatura, c.precio_eur, c.precio_usd, c.imagen 
            FROM cartas c JOIN ediciones e ON (c.idEdicion = e.idEdicion)
            WHERE c.nombre COLLATE utf8mb4_general_ci LIKE '%$nombre%'";  
        
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
              echo "<td class='containerCarta'>";
              echo $row["precio_eur"] . "€ | " . $row["precio_usd"] . "$ | " . $row["abreviatura"];
              echo "<div class='card'>";
              echo "<img src='http://" . $_SERVER['SERVER_ADDR'] . "/" . $imagenSegura . "' width='280' height='410' loading='eager' class='card' data-value='" . $row["idCarta"] . "' data-selected='false' style='border-radius: 10px;'>";
              echo "</div>";
              echo "<div> Copias deseadas: </div>";
              echo "<div class='number-input'>";
              // Aquí el name usa el idCarta como índice del array
              echo '<input type="number" name="cantidad[' . $row["idCarta"] . ']" value="0" min="0" class="cantidad-input" data-precio="' . $row["precio_eur"] . '">';
              echo "</div>";
              echo "</td>";
              $count = $count + 1;
              $i=$i+1;
              
            }
        }
        
        echo "</table>";   
        echo "<input type='hidden' id='selectedImages' name='selectedImages' value='[]'>";
        echo "<div id='totalContainer' style='font-size: 20px; margin: 10px 0;'>Total: 0 €</div>";
        echo "<div class='botones-container'>";
        echo "<button type='submit' class='botonSave' name='metodo' value=" . "insert" . ">Guardar Cartas en Inventario</button>";
        echo "</div>";
        echo "</form>";   
        echo "<script src='../js/selectCards.js'></script>";
        echo "<script src='../js/selectBuyingCards.js'></script>";

        return NULL;
    }
        
    if($_SESSION["edicion_busqueda_index"]){

      // Recuperar edicion de busqueda
      $abreviatura = $_SESSION["edicion_busqueda_index"];
      $nombre = $_SESSION["nombreCarta"];
      $conn = conectar();
      showSearch($conn, $abreviatura, $nombre);
      $conn->close();
    }
    
?>

</body>