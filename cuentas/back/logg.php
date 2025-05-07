<?php
session_start();

// Verifica si hay un mensaje en la sesión
if (isset($_SESSION['mensaje'])) {
    echo "<script>
          alert('{$_SESSION['mensaje']}');
          </script>";

    // Una vez mostrado, elimina el mensaje de la sesión para que no se muestre de nuevo
    unset($_SESSION['mensaje']);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../front/inicio/img/flavicon-01.png" rel="icon">
  <link href="../front/inicio/img/flavicon-01.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../front/inicio/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../front/inicio/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../front/inicio/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../front/inicio/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../front/inicio/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../front/inicio/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../front/inicio/vendor/simple-datatables/style_logg.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="../front/inicio/css/style_logg.css" rel="stylesheet">

  <style>
    .credits-container {
      text-align: center;
      margin-top: 20px; /* Ajusta el margen superior según sea necesario */
    }

    .btn-back,
    .logout-button {
      background: none;
      border: none;
      padding: 0;
    }

    .btn-back img,
    .logout-button img {
      display: block;
      width: 100px;
      height: auto;
    }
    
  </style>
</head>

<body>
  <a href="javascript:history.go(-1)" class="btn-back">
    <img src="../front/inicio/img/volver-01-01-01.png" alt="Volver">
  </a>

  <main>
    <div class="container">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="../../index.html" class="logo d-flex align-items-center w-auto">
                  <img src="../front/inicio/img/flavicon-01.png" alt="">
                  <span class="d-none d-lg-block">CESMAPS</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Ubicacion & Publicidad</h5>
                    <p class="text-center small">Ingresa tu usuario y contraseña para iniciar sesión</p>
                  </div>

                  <form class="row g-3 needs-validation" action="loguear.php" method="post" novalidate>

                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Correo Electronico</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control" id="yourUsername" required>
                        <div class="invalid-feedback">Por favor ingresa tu usuario!</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Contraseña</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Por favor ingresa tu contraseña!</div>
                    </div>

                  
                    <div class="col-12">
                      <button class="btn btn-primary w-100" name="ingresar" type="submit">Ingresar</button>
                    </div>
                    </form>
                    <div class="col-12 mt-2">
                      <a href="createUser.php" class="btn btn-primary w-100">Crear Cuenta</a>
                    </div>
                </div>
              </div>

              <div class="credits-container">                
               Derechos de autor <strong><span>Encryption</span></strong>. Todos los derechos reservados
              </div>

              

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../front/inicio/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../front/inicio/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../front/inicio/vendor/chart.js/chart.umd.js"></script>
  <script src="../front/inicio/vendor/echarts/echarts.min.js"></script>
  <script src="../front/inicio/vendor/quill/quill.min.js"></script>
  <script src="../front/inicio/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../front/inicio/vendor/tinymce/tinymce.min.js"></script>
  <script src="../front/inicio/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../front/inicio/js/main.js"></script>

<!-- Botón flotante -->
<button id="toggleButton" onclick="toggleBanner()" class="floating-button rounded-circle btn-primary">
  <i class="bi bi-bell" style="font-size: 24px;"></i>
</button>

<!-- Contenedor del banner flotante (inicialmente visible) -->
<div class="floating-banner" id="floatingBanner">
<?php
      // PHP code to fetch and display floating banner
      include("../../base de datos/con_db.php");
      // Verifica si la conexión fue exitosa
      if ($conex->connect_error) {
        die("Error de conexión: " . $conex->connect_error);
      }
      // Consulta para obtener una publicación aleatoria que sea una imagen o gif
      $sql1 = "SELECT * FROM publicaciones WHERE estado = '0' AND tipo_archivo <> 'video/mp4' AND ancho_archivo < alto_archivo ORDER BY RAND() LIMIT 1";
      $result1 = $conex->query($sql1);
      // Verifica si se encontraron resultados
      if ($result1->num_rows > 0) {
        while ($row = $result1->fetch_assoc()) {
          $ruta_archivo = '../../publicaciones/back/' . $row['ruta_archivo'];
          echo '<div class="banner-content">';
          echo '<img src="' . $ruta_archivo . '" alt="Banner Image">';
          echo '</div>';
        }
      } else {
        echo "No se encontraron imágenes.";
      }
      $conex->close();
    ?>
</div>

<Script>
function toggleBanner() {
  var banner = document.querySelector('.floating-banner');
  banner.classList.toggle('hidden');
}
</Script>
</body>

</html>