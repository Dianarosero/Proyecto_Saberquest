<?php
session_start();
include("../../base de datos/con_db.php");

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID de juego no proporcionado.";
    header("Location: ver_juegos.php");
    exit();
}

$id = $_GET['id'];

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['gameName'] ?? '');
    $descripcion = trim($_POST['gameDescription'] ?? '');
    $url = trim($_POST['gameUrl'] ?? '');
    $imagen = trim($_POST['gameImage'] ?? '');

    // Validar datos
    if (empty($nombre) || empty($descripcion) || empty($url) || empty($imagen)) {
        $_SESSION['error_message'] = "Por favor, completa todos los campos obligatorios.";
    } else {
        // Actualizar juego en la base de datos
        $query = "UPDATE juegos SET nombre = ?, descripcion = ?, imagen = ?, url = ? WHERE id = ?";
        $stmt = $conex->prepare($query);
        if (!$stmt) {
            $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conex->error;
        } else {
            $stmt->bind_param("ssssi", $nombre, $descripcion, $imagen, $url, $id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Juego actualizado correctamente.";
                header("Location: ver_juegos.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Error al actualizar el juego: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Obtener datos actuales del juego
$query = "SELECT nombre, descripcion, imagen, url FROM juegos WHERE id = ?";
$stmt = $conex->prepare($query);
if (!$stmt) {
    $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conex->error;
    header("Location: ver_juegos.php");
    exit();
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Juego no encontrado.";
    header("Location: ver_juegos.php");
    exit();
}

$juego = $result->fetch_assoc();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Juego</title>
    <meta name="description" content="Editar juego educativo para la plataforma SaberQuest.">
    <link rel="stylesheet" href="../../assets/src_juegos/css/ver_juegos.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <style>
        /* Estilos para el formulario */
        .form-wrapper {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333333;
        }
        
        .required {
            color: #B22222;
            margin-left: 4px;
        }
        
        input[type="text"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
        }
        
        input[type="text"]:focus,
        input[type="url"]:focus,
        textarea:focus {
            border-color: #003366;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }
        
        .error-msg {
            color: #B22222;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .helper-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .form-actions {
            margin-top: 30px;
            display: flex;
            gap: 10px;
        }
        
        .btn-cancel {
            background-color: #E0E0E0;
            color: #333;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }
        
        .btn-submit {
            background-color: #003366;
            color: #FFFFFF;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            flex: 1;
        }
        
        .btn-submit:hover {
            background-color: #004488;
        }
        
        .btn-cancel:hover {
            background-color: #D0D0D0;
        }
        
        /* Alerta de mensajes */
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="logo">
                    <a href="../index_admin.php">
                        <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                    </a>
                </div>
                <nav class="nav">
                    <a href="../index_admin.php" class="nav-link">Inicio</a>
                    <a href="ver_juegos.php" class="nav-link">Juegos</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">EDITAR JUEGO</h1>
            
            <div class="form-wrapper">
                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert error">
                        <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <form id="gameForm" action="editar_juego.php?id=<?php echo htmlspecialchars($id); ?>" method="POST">
                    <div class="form-group">
                        <label for="gameName">Nombre<span class="required">*</span></label>
                        <input type="text" id="gameName" name="gameName" placeholder="Ingresa el nombre del juego" value="<?php echo htmlspecialchars($juego['nombre']); ?>" required>
                        <small class="error-msg" id="gameNameError"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gameDescription">Descripción <span class="required">*</span></label>
                        <textarea id="gameDescription" name="gameDescription" rows="4" placeholder="Describe el propósito y funcionamiento del juego" required><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>
                        <small class="error-msg" id="gameDescriptionError"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gameImage">URL de la imagen <span class="required">*</span></label>
                        <input type="text" id="gameImage" name="gameImage" placeholder="https://ejemplo.com/imagen.jpg" value="<?php echo htmlspecialchars($juego['imagen']); ?>" required>
                        <small class="error-msg" id="gameImageError"></small>
                        <small class="helper-text">URL de la imagen para mostrar como portada del juego.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="gameUrl">Link del juego <span class="required">*</span></label>
                        <input type="url" id="gameUrl" name="gameUrl" placeholder="https://view.genially.com/mi-juego" value="<?php echo htmlspecialchars($juego['url']); ?>" required>
                        <small class="error-msg" id="gameUrlError"></small>
                    </div>
                    
                    <div class="form-actions">
                        <a href="ver_juegos.php" class="btn-cancel">Cancelar</a>
                        <button type="submit" class="btn-submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript para validación de formulario -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('gameForm');
            const nameInput = document.getElementById('gameName');
            const descInput = document.getElementById('gameDescription');
            const imgInput = document.getElementById('gameImage');
            const urlInput = document.getElementById('gameUrl');
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Validar nombre
                if (nameInput.value.trim() === '') {
                    document.getElementById('gameNameError').textContent = 'El nombre del juego es obligatorio';
                    isValid = false;
                } else {
                    document.getElementById('gameNameError').textContent = '';
                }
                
                // Validar descripción
                if (descInput.value.trim() === '') {
                    document.getElementById('gameDescriptionError').textContent = 'La descripción es obligatoria';
                    isValid = false;
                } else {
                    document.getElementById('gameDescriptionError').textContent = '';
                }
                
                // Validar URL de imagen
                if (imgInput.value.trim() === '') {
                    document.getElementById('gameImageError').textContent = 'La URL de imagen es obligatoria';
                    isValid = false;
                } else {
                    document.getElementById('gameImageError').textContent = '';
                }
                
                // Validar URL del juego
                if (urlInput.value.trim() === '') {
                    document.getElementById('gameUrlError').textContent = 'El link del juego es obligatorio';
                    isValid = false;
                } else {
                    document.getElementById('gameUrlError').textContent = '';
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>