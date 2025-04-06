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

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="index.css">
  <title>Home</title>
  <link rel="icon" href="mtgLogo.ico" type="image/x-icon">
  <link rel="shortcut icon" href="mtgLogo.ico" type="image/x-icon">
</head>

<body>
<div class="main">  
  <div class="sidebar">  <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="my_cards.php">My cards</a></li>
    <li><a href="forSale.php">For Sale</a></li>
    <li><a href="wanted.php">Wanted</a></li>
    <li><a href="decks.php">Decks</a></li>
    <li><a href="opciones.php">Options</a></li>
    <form action=""><li><a href="cerrarSesion.php">Log out</a></li></form>
  </ul></div>

  <div class="contenido">
    <div class="imgBackground">
      <div class="img">
        <img src="mtgLogo.png" height="15%" width="15%">
        <br>
      </div>
    </div>

    <hr>

    <h1>Search</h1>
    <hr>
    
    <form action="search.php" method="post">
      <table border="1">
          <tr>
              <th><input type="text" name="name" id="name" placeholder="Name"></th>
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

              <?php
                if($_SESSION['flag']==1){ //flag indica si se está realizando una busqueda
                  

                  echo "<table border='1'>";
                  echo "<tr><th>nombre</th><th>numero</th><th>edicion</th><th>precio €</th><th>precio $</th></tr>";
                  // Imprimir datos de cada fila
                
                  echo "<tr><td>";
                  echo $_SESSION["nombre"];
                  echo "</td><td>";
                  echo $_SESSION["numCarta"];
                  echo "</td><td>";
                  echo $_SESSION["edicion"];
                  echo "</td><td>";
                  echo $_SESSION["precioEUR"];
                  echo "</td><td>";
                  echo $_SESSION["precioUSD"];
                  echo"</td></tr>";
                  echo "</table>";
                  echo "<img src='imagenRedirect.php' width=350 height= 470 loading='eager'>";    
                  
                }
                $_SESSION["flag"]=0;
              ?>

          </tr>
      </table> 
    </form>
  </div>
</div>  
</body>
</html>