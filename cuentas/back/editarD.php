<?php
include('../../base de datos/sesiones.php');

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

  <title>Editar</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../front/editar/img/flavicon-01.png" rel="icon">
  <link href="../front/editar/img/flavicon-01.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../front/editar/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../front/editar/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../front/editar/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../front/editar/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../front/editar/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../front/editar/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../front/editar/vendor/simple-datatables/style_editarD.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="../front/editar/css/style_editarD.css" rel="stylesheet">
  <style>
    .container {
    max-width: 1400px; /* Cambié el tamaño máximo del contenedor a 1400px */
  }
  .form-group {
    margin-bottom: 30px; /* Añadí más espacio entre los campos de formulario */
  }
  .form-group > .col-md-6 {
    margin-bottom: 30px; /* Añadí más espacio entre las columnas */
  }
  .form-group > .col-md-6:not(:last-child) {
    margin-right: 40px; /* Añadí espacio entre las columnas */
  }

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
            $contrasena="";
            $tUsuario="";
            $identiUS=$_SESSION['usuario'];
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
                    $contrasena=$arr['password'];
                    $tUsuario=$arr['tipo_usuario'];
                }
            }

            if (isset($_POST['actualizar'])) {
              $name = $_POST['nombres'];
              $apellido = $_POST['apellidos'];
              $n_telefono = $_POST['num_telefono'];
              $tID = $_POST['cboTipoid'];
              $n_id = $_POST['num_identificacion'];
              //$email = $_POST['email'];
              //$contrasena = $_POST['password'];
              $tUsuario = $_POST['cboTipoUsuarios'];
      
              $sql = "UPDATE usuario SET nombre='$name', apellido='$apellido', email='$email',
                      password='$contrasena', num_ID='$n_id', tipo_id='$tID', teléfono='$n_telefono',
                      tipo_usuario='$tUsuario' WHERE email='$identiUS'";
      
              // Obtener el número de ID actual del usuario
              $query_usuario_actual = mysqli_query($conex, "SELECT num_ID FROM usuario WHERE email='$identiUS'");
              $row_usuario_actual = mysqli_fetch_assoc($query_usuario_actual);
              $n_id_actual = $row_usuario_actual['num_ID'];
      
              //verificar que el num_id no se repita en BD
              $verificar_id = mysqli_query($conex, "SELECT * FROM usuario WHERE num_ID='$n_id' ");
              //$verificar_id2=mysqli_query($conex, "SELECT*FROM usuario WHERE num_ID!='$n_id' ");
              if (mysqli_num_rows($verificar_id) > 0 && $n_id != $n_id_actual) {
                  session_start();
                  $_SESSION['mensaje'] = 'Número de identificación ya existe';
                  header('Location: editarD.php');
                  exit;
              } else {
                  // Ejecutar la consulta SQL
                  $resultadoo = mysqli_query($conex, $sql);
                  if ($resultadoo) {
                      echo "<script>alert('Sus datos han sido actualizados correctamente'); window.location.href = 'visualizarInf.php';</script>";
                  } else {
                      echo "Error al ejecutar la consulta: " . mysqli_error($conex);
                  }
              }
          }
        
      ?>
        
<?php
    if($_SESSION['usuario'] == 'administracion@gmail.com'){
        echo '<a href="../../cuentas/back/bienvenida/back/welcome.php" class="btn-back">
        <img src="../front/editar/img/volver-01-01-01.png" alt="Volver">
      </a>';
    }else{
        echo '<a href="../../cuentas/back/bienvenida/back/welcomeUser.php" class="btn-back">
        <img src="../front/editar/img/volver-01-01-01.png" alt="Volver">
      </a>';
    }
?>
  

  <main>
    <div class="container">
  
      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 d-flex flex-column align-items-center justify-content-center">
  
              <div class="d-flex justify-content-center py-4 align-items-center">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="../front/editar/img/flavicon-01.png" alt="" style="margin-right: 10px;"> <!-- Añade un margen a la derecha del icono -->
                  <span>CESMAPS</span>
                </a>
              </div><!-- End Logo -->
  
              <div class="card mb-3">
  
                <div class="card-body">
  
                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Editar Información</h5>
                    <p class="text-center small">Edita los datos personales de tu cuenta</p>
                  </div>
  
                  <form class="needs-validation" method="POST" novalidate>
                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="nombres" class="form-label">Nombres</label>
                        <input type="text" value="<?= $name; ?>" name="nombres" class="form-control" id="nombres" required>
                        <div class="invalid-feedback">Por favor, ingresa tus nombres.</div>
                      </div>
  
                      <div class="col-md-6 mb-4">
                        <label for="cboTipoid" class="form-label">Tipo de Identificación</label>
                        <select name="cboTipoid" value="<?= $tID; ?>" class="form-select" id="cboTipoid" required>
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
                        <input type="text"  value="<?= $apellido; ?>" name="apellidos" class="form-control" id="apellidos" required>
                        <div class="invalid-feedback">Por favor, ingresa tus apellidos.</div>
                      </div>
  
                      <div class="col-md-6 mb-4">
                        <label for="num_identificacion" class="form-label">Número de Identificación</label>
                        <input type="text" value="<?= $n_id; ?>" name="num_identificacion" class="form-control" id="num_identificacion" pattern="[0-9]+" required>
                        <div class="invalid-feedback">Por favor, ingresa tu número de identificación.</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="cboTipoUsuarios" class="form-label">Tipo de Usuario</label>
                        <select  value="<?= $tUsuario; ?>" name="cboTipoUsuarios" class="form-select" id="cboTipoUsuarios" required>
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
                        <input type="text" value="<?= $n_telefono; ?>" name="num_telefono" class="form-control" id="num_telefono" pattern="[0-9]+" required>
                        <div class="invalid-feedback">Por favor, ingresa tu número de teléfono.</div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-4">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" value="<?= $email; ?>" name="email" class="form-control" id="email" required>
                        <div class="invalid-feedback">Por favor, ingresa un correo electrónico válido.</div>
                      </div>

                      <div class="col-md-6 mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" value="<?= $contrasena; ?>" name="password" class="form-control" id="password" required>
                        <div class="invalid-feedback">Por favor, ingresa una contraseña.</div>
                      </div>
                    </div>
  
                    <div class="col-12 mt-4">
                      <button class="btn btn-primary w-100" type="submit" name="actualizar">Editar</button>
                    </div>
                   
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
         <img src="../front/editar/img/cerrar_sesion-01.png" alt="Cerrar Sesión">
      </a>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../front/editar/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../front/editar/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../front/editar/vendor/chart.js/chart.umd.js"></script>
  <script src="../front/editar/vendor/echarts/echarts.min.js"></script>
  <script src="../front/editar/vendor/quill/quill.min.js"></script>
  <script src="../front/editar/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../front/editar/vendor/tinymce/tinymce.min.js"></script>
  <script src="../front/editar/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../front/editar/js/main.js"></script>

  <!-- Custom JavaScript for validating number inputs -->
  <script>
    // Función para validar que solo se ingresen números en un campo de entrada
    function validarNumeros(input) {
      input.value = input.value.replace(/\D/g, ''); // Elimina cualquier carácter que no sea un número
    }

    // Asigna la función de validación al evento de entrada para los campos de número de identificación y número de teléfono
    document.getElementById('num_identificacion').addEventListener('input', function() {
      validarNumeros(this);
    });

    document.getElementById('num_telefono').addEventListener('input', function() {
      validarNumeros(this);
    });

    
  </script>

</body>

</html>
