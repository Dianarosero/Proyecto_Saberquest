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
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Formulario de Registro</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card shadow">
          <div class="card-header text-center bg-primary text-white">
            <h4>Registro de Usuario</h4>
          </div>
          <div class="card-body">
            <form action="crear_usuario.php" method="post" novalidate>
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input name="nombres" type="text" class="form-control" id="nombre" placeholder="Ingresa tu nombre" required>
              </div>
              <div class="mb-3">
                <label for="codigo" class="form-label">Código Estudiantil</label>
                <input name="num_identificacion" type="text" class="form-control" id="codigo" placeholder="Ej: 12345678" required>
              </div>
              <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input name="contraseña" type="password" class="form-control" id="contrasena" placeholder="Crea una contraseña" required>
              </div>
              <div class="mb-3">
                <label for="tipoUsuario" class="form-label">Tipo de Usuario</label>
                <select class="form-select" id="tipoUsuario" name="cboTipoUsuarios" required>
                    <option value="">Seleccione...</option>
                    <option value="3">Estudiante</option>
                    <option value="2">Docente</option>
                </select>

              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-success">Registrarse</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS (opcional, para funcionalidades dinámicas) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
