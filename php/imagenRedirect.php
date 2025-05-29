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

  header('Content-Type: image/jpg');
  $imagen = $_SESSION["imagen"];

  // Actualizar la edicion que se busca mediante API REST en python
  $data = ['imagen' => "$imagen"];
  $options = [
    'http' => [
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
  ];
  $context = stream_context_create($options);
  $response = file_get_contents('http://localhost:5000/img/', false, $context);

  // Decodificar JSON recibido de Flask
  $response_data = json_decode($response, true);

  if (isset($response_data["ruta"])) {
    $ruta_imagen = $response_data["ruta"];
    echo "<img src='$ruta_imagen'>";
  } else {
    echo "[!] 404 not found :(";
  };
  
  header("Location: index.php");

?>
