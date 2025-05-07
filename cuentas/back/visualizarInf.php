<?php
include('../../base de datos/sesiones.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Visualizar</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../front/visualizar/img/flavicon-01.png" rel="icon">
  <link href="../front/visualizar/img/flavicon-01.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../front/visualizar/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../front/visualizar/vendor/simple-datatables/style_visualizar.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="../front/visualizar/css/style_visualizar.css" rel="stylesheet">
  <style>
  .btn-back,
  .logout-button {
    background: none; /* Quita cualquier fondo del botón */
    border: none; /* Quita cualquier borde del botón */
    padding: 0; /* Quita cualquier relleno del botón */
  }

  /* Estilo para las imágenes en los botones */
  .btn-back img,
  .logout-button img {
    display: block; /* Asegura que la imagen sea un bloque para controlar su tamaño */
    width: 100px; /* Establece el ancho de la imagen */
    height: auto; /* Permite que la altura se ajuste automáticamente según el ancho */
  }
  </style>
</head>

<body>
<?php
            //conexion con la base de datos
            include("../../base de datos/con_db.php");

            //datos del usuario
            $name="";
            $apellido="";
            $n_telefono="";
            $tID="";
            $n_id="";
            $email="";
            $tUsuario="";
            $identiUS=$_SESSION['usuario'];
           // echo $_SESSION['usuario'];
            $query_usuario=mysqli_query($conex, "SELECT * FROM usuario where email='$identiUS'");
            $row_usuario=mysqli_num_rows($query_usuario);
            //session_start();
            if(!isset($_SESSION['usuario'])){
            echo "redirigir al login";
            header('Location: logg.php');
            }
            

            if($row_usuario=$identiUS){
                while($arr=mysqli_fetch_assoc($query_usuario)){
                    $name=$arr['nombre'];
                    $apellido=$arr['apellido'];
                    $n_telefono=$arr['teléfono'];
                    $tID=$arr['tipo_id'];
                    $n_id=$arr['num_ID'];
                    $email=$arr['email'];
                    $tUsuario=$arr['tipo_usuario'];
                }
            }
        ?>



<?php
    if($_SESSION['usuario'] == 'administracion@gmail.com'){
        echo '<a href="../../cuentas/back/bienvenida/back/welcome.php" class="btn-back">
        <img src="../front/visualizar/img/volver-01-01-01.png" alt="Volver">
      </a>';
    }else{
        echo '<a href="../../cuentas/back/bienvenida/back/welcomeUser.php" class="btn-back">
        <img src="../front/visualizar/img/volver-01-01-01.png" alt="Volver">
      </a>';
    }
?>

  
  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 d-flex flex-column align-items-center justify-content-center">
  
              <div class="d-flex justify-content-center py-4 align-items-center">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="../front/visualizar/img/flavicon-01.png" alt="" style="margin-right: 10px;"> <!-- Añade un margen a la derecha del icono -->
                  <span>CESMAPS</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">     
                      <div class="pt-4 pb-2">
                        <h5 class="card-title text-center pb-0 fs-4">Visualizar información</h5>
                        <p class="text-center small">Visualizar datos personales de tu cuenta</p>
                      </div>

                  <!-- Formulario de Información -->
                  <form class="needs-validation" action= "editarD.php"novalidate>
                    <!-- Primera fila -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="nombres" class="form-label">Nombre</label>
                        <input type="text" value="<?= $name; ?>" name="nombres" class="form-control" id="nombres" value="<?= $name; ?>" required disabled>
                      </div>
                      <div class="col-md-6">
                        <label for="cboTipoid" class="form-label">Tipo de Identificación</label>
                        <input type="text" value="<?= $tID; ?>" name="cboTipoid" class="form-control" id="cboTipoid" value="<?= $tID; ?>" required disabled>
                      </div>
                    </div>
                    <!-- Segunda fila -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="apellidos" class="form-label">Apellidos</label>
                        <input type="text" value="<?= $apellido; ?>" name="apellidos" class="form-control" id="apellidos" value="<?= $apellido; ?>" required disabled>
                      </div>
                      <div class="col-md-6">
                        <label for="num_identificacion" class="form-label">Número de Identificación</label>
                        <input type="text" value="<?= $n_id; ?>" name="num_identificacion" class="form-control" id="num_identificacion" value="<?= $n_id; ?>" required disabled>
                      </div>
                    </div>
                    <!-- Tercera fila -->
                    <div class="row mb-3">
                      <div class="col-md-6">
                        <label for="cboTipoUsuarios" class="form-label">Tipo de Usuario</label>
                        <input type="text" value="<?= $tUsuario; ?>" name="cboTipoUsuarios" class="form-control" id="cboTipoUsuarios" value="<?= $tUsuario; ?>" required disabled>
                      </div>
                      <div class="col-md-6">
                        <label for="num_telefono" class="form-label">Número de Teléfono</label>
                        <input type="text" value="<?= $n_telefono; ?>" name="num_telefono" class="form-control" id="num_telefono" value="<?= $n_telefono; ?>" required disabled>
                      </div>
                    </div>
                    <!-- Cuarta fila -->
                    <div class="row mb-3">
                      <div class="col-md-12">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" value="<?= $email; ?>" name="email" class="form-control" id="email" value="<?= $email; ?>" required disabled>
                      </div>
                    </div>
                    <!-- Botón de Edición -->
                    <div class="row">
                      <div class="col-md-12">
                        <button class="btn btn-primary w-100"  type="submit" name="register">Editar información</button>
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
    
  </main><!-- End #main -->

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

      <a href="../../base de datos/cerrar.php" class="logout-button">
        <img src="../front/visualizar/img/cerrar_sesion-01.png" alt="Cerrar Sesión">
      </a>

  <!-- Vendor JS Files -->
  <script src="../front/visualizar/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../front/visualizar/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../front/visualizar/vendor/chart.js/chart.umd.js"></script>
  <script src="../front/visualizar/vendor/echarts/echarts.min.js"></script>
  <script src="../front/visualizar/vendor/quill/quill.min.js"></script>
  <script src="../front/visualizar/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../front/visualizar/vendor/tinymce/tinymce.min.js"></script>
  <script src="../front/visualizar/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../front/visualizar/js/main.js"></script>

</body>

</html>
