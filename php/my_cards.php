<?php
  session_start();

  if($_SESSION['username'] != 'mario') {
    echo '
      <script>
        alert("Access denied");
        window.location = "index.php"
      </script>
    ';
  }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My Cards</title>
        <link rel="stylesheet" type="text/css" href="my_cards.css">
    </head>
    <body>
    <div class="main">  
        <div class="sidebar">  
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="my_cards.php">My cards</a></li>
            <li><a href="forSale.php">For Sale</a></li>
            <li><a href="wanted.php">Wanted</a></li>
            <li><a href="decks.php">Decks</a></li>
            <li><a href="opciones.php">Options</a></li>
            <form action=""><li><a href="cerrarSesion.php">Log out</a></li></form>
          </ul>
        </div>
      
        <div class="contenido">
          <div class="imgBackground">
            <div class="img">
                <img src="mtgLogo.png" height="20%" width="25%">
                <br>
            </div>
          </div>

          <h1>Mario's Cards</h1>
          <form action="" method="post">
            <table border="1">
              <tr>
                <th><select name="edicion" id="edicion">
                    <option value="null">Edition</option>
                    <option value="OTJ">Outlaws of thunder Junction </option>
                    <option value="PIP">Fallout</option>
                    <option value="MKM">Murders at Karlov Manor</option>
                    <option value="RVR">Ravnica Remastered</option>
                    <option value="LCI">The Lost caverns of Ixalan</option>
                    <option value="WHO">Doctor Who</option>
                    <option value="WOE">Wilds of Eldraine</option>
                    <option value="WOT">Wilds of Eldraine: Enchanting Tales</option>
                    <option value="CMM">Commander Masters</option>
                    <option value="LTR">The Lord of the Rings</option>
                    <option value="MAT">March of the Machine: The Aftermath</option>
                    <option value="MOM">March of the Machine</option>
                    <option value="ONE">Phyrexia: All Will Be One</option>
                    <option value="DMR">Dominaria remastered</option>
                    <option value="BRO">The Brother's War</option>
                    <option value="40K">Warhammer 40.000 Commander</option>
                    <option value="DMU">Dominaria United</option>
                    <option value="SIR">Shadows over Innistrad Remastered</option>
                    <option value="BOT">Transformers</option>
                    <option value="2X2">Double Masters 2022</option>
                    </select></th>
                <th><input type="submit" value="Search"></th>
              </tr>
            </table> 
          </form>
          <hr>
          
            <?php

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                  // Recuperar el valor del formulario
                  $edicion = $_POST["edicion"];                  

                  // Conexión a la base de datos
                  $servername = "localhost";
                  $db_username = "root"; // Usuario de la base de datos
                  $db_password = ""; // Contraseña de la base de datos
                  $dbname = "mtg";
                  $conn = new mysqli($servername, $db_username, $db_password, $dbname);

                  // Verificar la conexión
                  if ($conn->connect_error) {
                    die("Error en la conexión: " . $conn->connect_error);
                  }

                  // Actualizar la edicion que se busca mediante la BD
                  $sql2="UPDATE edicion_busqueda SET edicion='$edicion' WHERE id = 1";
                  if($conn->query($sql2) === FALSE){
                    echo "Error al actualizar";
                  }

                  // Consulta SQL para obtener todo el listado de cartas
                  $sql = "SELECT id, imagen, precio_eur, precio_usd FROM cartas where edicion = '$edicion' ORDER BY num_carta";
                  $result = $conn->query($sql);
                  
                  // Imprimir datos de cada fila
                  if (mysqli_num_rows($result) > 0) {
                    echo "<form action='obtenerImagenSelect.php' method='post'>";
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
                      // El id del div es igual que el id en la base de datos, para poder recuperarlo con onclick()
                      echo "<td>" . $row["precio_eur"] . "€ | " . $row["precio_usd"] . "$ <div class='card'>
                      <img src=http://127.0.0.1/img/$i width=300 height= 430 loading='eager' border-radius: 10px class='card' data-value='" . $row["id"] . "'data-selected='false'>
                      </div></td>";
                      
                      $count = $count + 1;
                      $i=$i+1;
                      
                    }
                  }
                    echo "</table>";   
                    echo "<input type='hidden' id='selectedImages' name='selectedImages' value='[]'>";
                    echo "<button type='submit'>Guardar Cartas</button>";
                    echo "</form>";   
                    echo "<script src='selectCards.js'></script>";
                }
              ?>
        </div>
    </div>
</body>
</html>