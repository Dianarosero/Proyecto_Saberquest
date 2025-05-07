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
  <title>register</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <!-- Favicons -->
  <link href="../front/registro/img/flavicon-01.png" rel="icon">
  <link href="../front/registro/img/flavicon-01.png" rel="apple-touch-icon">
  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <!-- Vendor CSS Files -->
  <link href="../front/registro/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../front/registro/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../front/registro/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../front/registro/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../front/registro/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../front/registro/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../front/registro/vendor/simple-datatables/style_createU.css" rel="stylesheet">
  <!-- Template Main CSS File -->
  <link href="../front/registro/css/style_createU.css" rel="stylesheet">
  <style>
    .container {
      max-width: 1400px;
    }

    .form-group {
      margin-bottom: 30px;
    }

    .form-group>.col-md-6 {
      margin-bottom: 30px;
    }

    .form-group>.col-md-6:not(:last-child) {
      margin-right: 40px;
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
    <img src="../front/registro/img/volver-01-01-01.png" alt="Volver">
  </a>
  <main>
    <div class="container">
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 d-flex flex-column align-items-center justify-content-center">
              <div class="d-flex justify-content-center py-4 align-items-center">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="../front/registro/img/flavicon-01.png" alt="" style="margin-right: 10px;">
                  <span>CESMAPS</span>
                </a>
              </div>
              <div class="card mb-3">
                <div class="card-body">
                  <div class="pt-4 pb-4">
                    <h5 class="card-title text-center pb-0 fs-4">Crea tu cuenta</h5>
                    <p class="text-center small">Ingresa tus datos personales para crear una cuenta</p>
                  </div>
                  <form class="needs-validation" method="POST" action="crear.php" novalidate>
                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" name="nombres" class="form-control" id="nombres" required>
                        <div class="invalid-feedback">Por favor, ingresa tus nombres.</div>
                      </div>
                      <div class="col-md-6 mb-4">
                        <label for="cboTipoid" class="form-label">Tipo de Identificación</label>
                        <select name="cboTipoid" class="form-select" id="cboTipoid" required>
                          <option value="">Selecciona el tipo de Identificación</option>
                          <option value="cedula">CEDULA</option>
                          <option value="tarjeta_identidad">TARJETA DE IDENTIDAD</option>
                          <option value="codigo_estudiantil">CODIGO ESTUDIANTIL</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecciona un tipo de identificación.</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" name="apellidos" class="form-control" id="apellidos" required>
                        <div class="invalid-feedback">Por favor, ingresa tus apellidos.</div>
                      </div>
                      <div class="col-md-6 mb-4">
                        <label for="num_identificacion" class="form-label">Número de Identificación</label>
                        <input type="text" name="num_identificacion" class="form-control" id="num_identificacion" pattern="[0-9]+" title="Por favor, ingresa solo números" required>
                        <div class="invalid-feedback">Por favor, ingresa tu número de identificación.</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="cboTipoUsuarios" class="form-label">Tipo de Usuario</label>
                        <select name="cboTipoUsuarios" class="form-select" id="cboTipoUsuarios" required>
                          <option value="">Selecciona un tipo de usuario</option>
                          <option value="Estudiante">ESTUDIANTE</option>
                          <option value="Profesor">DOCENTE</option>
                          <option value="Administrativo">ADMINISTRATIVO</option>
                          <option value="Visitante">VISITANTE</option>
                        </select>
                        <div class="invalid-feedback">Por favor, selecciona un tipo de usuario.</div>
                      </div>
                      <div class="col-md-6 mb-4">
                        <label for="num_telefono" class="form-label">Número de Teléfono</label>
                        <input type="text" name="num_telefono" class="form-control" id="num_telefono" pattern="[0-9]+" title="Por favor, ingresa solo números" required>
                        <div class="invalid-feedback">Por favor, ingresa tu número de teléfono.</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" id="email" required>
                        <div class="invalid-feedback">Por favor, ingresa un correo electrónico válido.</div>
                      </div>
                      <div class="col-md-6 mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                        <div class="invalid-feedback">Por favor, ingresa una contraseña.</div>
                      </div>
                    </div>
                    <div class="col-12 mt-4">
                      <button class="btn btn-primary w-100" type="submit" name="register">Crear Cuenta</button>
                    </div>
                    <div class="col-12">
                      <div class="text-center mb-3">
                        <p class="small mb-0">¿Ya tienes una cuenta? <a href="logg.php">Inicia sesión</a></p>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              <div class="credits-container" style="text-align: center;">
                Derechos de autor <strong><span>Encryption</span></strong>. Todos los derechos reservados &copy; 2024
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
  
  <!-- Vendor JS Files -->
  <script src="../front/registro/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../front/registro/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../front/registro/vendor/chart.js/chart.umd.js"></script>
  <script src="../front/registro/vendor/echarts/echarts.min.js"></script>
  <script src="../front/registro/vendor/quill/quill.min.js"></script>
  <script src="../front/registro/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../front/registro/vendor/tinymce/tinymce.min.js"></script>
  <script src="../front/registro/vendor/php-email-form/validate.js"></script>
  <!-- Template Main JS File -->
  <script src="../front/registro/js/main.js"></script>
  <script>
    // Validación adicional para campos de número de identificación y número de teléfono
    document.addEventListener("DOMContentLoaded", function() {
      var forms = document.getElementsByClassName('needs-validation');
      var validation = Array.prototype.filter.call(forms, function(form) {
        form.addEventListener('submit', function(event) {
          if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
          }
          form.classList.add('was-validated');
          var num_identificacion = document.getElementById('num_identificacion').value;
          var num_telefono = document.getElementById('num_telefono').value;
          if (!/^\d+$/.test(num_identificacion)) {
            document.getElementById('num_identificacion').setCustomValidity("Por favor, ingresa solo números en el número de identificación.");
          } else {
            document.getElementById('num_identificacion').setCustomValidity('');
          }
          if (!/^\d+$/.test(num_telefono)) {
            document.getElementById('num_telefono').setCustomValidity("Por favor, ingresa solo números en el número de teléfono.");
          } else {
            document.getElementById('num_telefono').setCustomValidity('');
          }
        }, false);
      });
    });
  </script>

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
