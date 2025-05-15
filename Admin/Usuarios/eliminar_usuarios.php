<?php
session_start();
include("../../base de datos/con_db.php");

// Verificar si se ha proporcionado un ID de usuario
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = intval($_GET['id']);
$msg = '';
$error = '';

// Obtener la información del usuario
$sql = "SELECT id, nombre, codigo_estudiantil, id_rol FROM usuarios WHERE id = ?";
$stmt = $conex->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: usuarios.php");
    exit;
}

$usuario = $result->fetch_assoc();

// Procesar la eliminación cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    $sql_delete = "DELETE FROM usuarios WHERE id = ?";
    $stmt_delete = $conex->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    
    if ($stmt_delete->execute()) {
        header("Location: usuarios.php?eliminado=true");
        exit;
    } else {
        $error = "Error al eliminar el usuario: " . $conex->error;
    }
}

// Determinar el tipo de usuario para mostrar en el mensaje
$tipo_usuario = $usuario['id_rol'] == '2' ? 'docente' : 'estudiante';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Usuario</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
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
                        <li><a href="../index_admin.php" class="nav-link">Inicio</a></li>
                        <li><a href="usuarios.php" class="nav-link">Volver a Usuarios</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="confirmation-card animate-fade-in">
            <div class="confirmation-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h2 class="confirmation-title">Confirmar Eliminación</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="confirmation-message">
                <p>¿Estás seguro de que deseas eliminar el siguiente <?php echo $tipo_usuario; ?>?</p>
                <p class="mb-3 mt-3"><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre']); ?></p>
                <p class="mb-3"><strong>Código:</strong> <?php echo htmlspecialchars($usuario['codigo_estudiantil']); ?></p>
                <p class="text-center" style="color: var(--secondary); font-weight: bold;">Esta acción no se puede deshacer.</p>
            </div>
            
            <div class="confirmation-actions">
                <form method="post" action="">
                    <button type="submit" name="confirmar_eliminar" value="1" class="btn btn-secondary">
                        <i class="fas fa-trash-alt btn-icon"></i>Confirmar Eliminación
                    </button>
                </form>
                
                <a href="usuarios.php" class="btn btn-neutral">
                    <i class="fas fa-times btn-icon"></i>Cancelar
                </a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>
