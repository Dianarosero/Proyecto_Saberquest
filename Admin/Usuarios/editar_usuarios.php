<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

// Verificar si se ha proporcionado un ID de usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = intval($_GET['id']);
$msg = '';
$error = '';

// Obtener la información del usuario
$sql = "SELECT id, nombre, codigo_estudiantil, contraseña, id_rol, semestre FROM usuarios WHERE id = ?";
$stmt = $conex->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: usuarios.php");
    exit;
}

$usuario = $result->fetch_assoc();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar la contraseña actual
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $semestre = $_POST['semestre'] ?? '';

    // Si hay contraseña nueva, verificar la actual y el hash
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $error = "Debe proporcionar la contraseña actual para cambiar la contraseña.";
        } elseif (!password_verify($current_password, $usuario['contraseña'])) {
            $error = "La contraseña actual no es correcta.";
        } elseif ($new_password !== $confirm_password) {
            $error = "La nueva contraseña y la confirmación no coinciden.";
        } else {
            // Hashear la nueva contraseña antes de guardar
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET contraseña = ? WHERE id = ?";
            $stmt_update = $conex->prepare($sql_update);
            $stmt_update->bind_param("si", $hashed_new_password, $id);
            if ($stmt_update->execute()) {
                $msg = "Contraseña actualizada correctamente.";
            } else {
                $error = "Error al actualizar la contraseña: " . $conex->error;
            }
        }
    }

    // Si es estudiante y se proporciona un semestre, actualizarlo
    if ($usuario['id_rol'] == '3' && !empty($semestre)) {
        $sql_update = "UPDATE usuarios SET semestre = ? WHERE id = ?";
        $stmt_update = $conex->prepare($sql_update);
        $stmt_update->bind_param("si", $semestre, $id);
        if ($stmt_update->execute()) {
            if (empty($msg)) {
                $msg = "Semestre actualizado correctamente.";
            } else {
                $msg .= " Semestre actualizado correctamente.";
            }
        } else {
            if (empty($error)) {
                $error = "Error al actualizar el semestre: " . $conex->error;
            } else {
                $error .= " Error al actualizar el semestre: " . $conex->error;
            }
        }
    }

    // Obtener la información actualizada del usuario
    if (!empty($msg)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/src_usuarios/css/styles.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index_admin.php">
                        <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                    </a>
                </div>                
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="../index_admin.php#projects" class="nav-link">Inicio</a></li>
                        <li><a href="usuarios.php" class="nav-link">Volver a Usuarios</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <h1>Editar Usuario</h1>
        
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="form">
            <form id="edit-form" method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="codigo" class="form-label">Código:</label>
                        <input type="text" id="codigo" name="codigo" class="form-control" value="<?php echo htmlspecialchars($usuario['codigo_estudiantil']); ?>" disabled>
                    </div>
                    
                    <?php if ($usuario['id_rol'] == '3'): ?>
                    <div class="form-group">
                        <label for="semestre" class="form-label">Semestre:</label>
                        <input type="text" id="semestre" name="semestre" class="form-control" value="<?php echo htmlspecialchars($usuario['semestre']); ?>" placeholder="Ej: 2024-1">
                    </div>
                    <?php endif; ?>
                </div>
                
                <h2>Cambiar Contraseña</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="current_password" class="form-label">Contraseña Actual:</label>
                        <input type="password" id="current_password" name="current_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="form-label">Contraseña Nueva:</label>
                        <input type="password" id="new_password" name="new_password" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña:</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                    </div>
                </div>
                
                <div class="form-row mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save btn-icon"></i>Guardar Cambios
                    </button>
                    
                    <a href="eliminar_usuarios.php?id=<?php echo $usuario['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-trash-alt btn-icon"></i>Eliminar
                    </a>
                    
                    <a href="usuarios.php" class="btn btn-neutral">
                        <i class="fas fa-arrow-left btn-icon"></i>Volver
                    </a>
                </div>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2025 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../../assets/src_usuarios/js/scripts.js"></script>
</body>
</html>
