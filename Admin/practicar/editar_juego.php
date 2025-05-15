<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

// Obtener el ID del juego a editar
if (!isset($_GET['id'])) {
    header("Location: ver_juegos.php");
    exit;
}
$id = intval($_GET['id']);

// Obtener datos actuales del juego
$query = "SELECT * FROM juegos WHERE id = $id";
$result = mysqli_query($conex, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo "Juego no encontrado.";
    exit;
}
$juego = mysqli_fetch_assoc($result);

$error = "";
$mensaje = "";

// Procesar el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $url = $_POST['url'] ?? '';
    $imagen_ruta = $_POST['imagen_actual'] ?? $juego['imagen']; // Mantener la actual por defecto

    // Procesar la imagen si se ha subido una nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $carpeta_destino = "../../assets/src_juegos/img_juegos/";
        if (!is_dir($carpeta_destino)) {
            mkdir($carpeta_destino, 0777, true);
        }
        $nombre_archivo = uniqid() . "_" . basename($_FILES['imagen']['name']);
        $ruta_archivo = $carpeta_destino . $nombre_archivo;
        $tipo_archivo = strtolower(pathinfo($ruta_archivo, PATHINFO_EXTENSION));
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($tipo_archivo, $tipos_permitidos)) {
            $error = "Solo se permiten imágenes JPG, JPEG, PNG, GIF o WEBP.";
        } else {
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_archivo)) {
                $imagen_ruta = $ruta_archivo;
            } else {
                $error = "Error al subir la imagen.";
            }
        }
    }

    // Validar datos
    if (empty($nombre)) {
        $error = "El nombre del juego no puede estar vacío.";
    } elseif (empty($descripcion)) {
        $error = "La descripción del juego no puede estar vacía.";
    } elseif (empty($url)) {
        $error = "La URL del juego no puede estar vacía.";
    } elseif (empty($error)) {
        // Actualizar juego
        $stmt_update = $conex->prepare("UPDATE juegos SET nombre = ?, descripcion = ?, imagen = ?, url = ? WHERE id = ?");
        $stmt_update->bind_param("ssssi", $nombre, $descripcion, $imagen_ruta, $url, $id);
        if ($stmt_update->execute()) {
            $mensaje = "Juego actualizado correctamente.";
            $juego['nombre'] = $nombre;
            $juego['descripcion'] = $descripcion;
            $juego['imagen'] = $imagen_ruta;
            $juego['url'] = $url;
        } else {
            $error = "Error al actualizar el juego: " . $conex->error;
        }
        $stmt_update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Editar Juego</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Estilos de editar_formulario.php -->
    <style>
        :root {
            --primary: #003366;
            --primary-light: #003366;
            --secondary: #B22222;
            --secondary-light: #d93636;
            --accent: #FFD700;
            --accent-light: #FFE44D;
            --background: #003366;
            --text: #333333;
            --text-light: #666666;
            --neutral: #E0E0E0;
            --neutral-light: #F7F7F7;
            --success: #27ae60;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --accent-color: #ffffff;
            --transition: all 0.3s ease;
            --gap: 1.5rem;
        }
        
        body {
            background: #f4f6fb;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            padding: 35px 40px 30px 40px;
        }
        h1 {
            text-align: center;
            color: #263159;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 22px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #263159;
            font-weight: 600;
        }
        input[type="text"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1.5px solid #bfc6d1;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
            background: #f7f9fc;
        }
        input[type="text"]:focus,
        input[type="url"]:focus,
        textarea:focus {
            border-color: #003366;
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 90px;
        }
        input[type="file"] {
            margin-top: 8px;
        }
        .btn {
            background: #003366;
            color:rgb(255, 255, 255);
            border: none;
            padding: 13px 24px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn:hover {
            background: #003366;
        }
        .message {
            margin-bottom: 18px;
            padding: 13px 15px;
            border-radius: 6px;
            font-size: 1rem;
        }
        .error {
            background: #ffe6e6;
            color: #b30000;
            border: 1px solid #ffb3b3;
        }
        .success {
            background: #e6ffe6;
            color: #267326;
            border: 1px solid #b3ffb3;
        }
        .img-preview {
            max-width: 220px;
            max-height: 160px;
            display: block;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #bfc6d1;
            object-fit: contain;

        }
        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(8px);
            opacity: 0.12;
            z-index: -1;
            background-image: url('https://pixabay.com/get/g8386919d873394672d9c4f2b4a58bfdf6ddbc88918bd7be5af792f69144340e15c8134ca7a5df3c89411ba0b9f15bc66048659caff8143cbeee94118364b59da_1280.jpg');
        }
        
        .header {
            background-color: var(--primary);
            color: white;
            padding: 20px 0; /* Espaciado lateral moderado */
            width: 100%;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            left: 0;
            z-index: 1000;/* Altura mínima para un header elegante */
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-img {
            height: 50px;
        }
        .nav-list {
        display: flex;
        gap: 30px;
        }

        .nav-link {
            font-size: 1rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            padding-bottom: 5px;
            position: relative;
        }
        
        .nav-link:hover {
            color: var(--accent-color);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent-color);
            transition: var(--transition);
        }
        
        .nav-link:hover::after {
            width: 100%;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 2rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .footer {
            margin-top: auto;
            background-color: var(--primary);
            color: white;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .footer a {
            color: var(--accent);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer a:hover {
            color: var(--accent-light);
            text-decoration: underline;
        }

        .back-link:hover {
            color: var(--primary-light);
            transform: translateX(-5px);
        }
        @media (max-width: 600px) {
            .header {
                padding: 0 12px;
                min-height: 56px;
            }
            .logo-img {
                height: 36px;
            }
            .nav-link {
                font-size: 1rem;
                margin-left: 10px;
            }
        }


        

    </style>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
</head>
<body>
    <div class="bg-container"></div>
        <header class="header">
            <div class="logo">
                <a href="../index_admin.php">
                    <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                </a>
            </div>
            <nav class="nav">
                <a href="../index_admin.php#projects" class="nav-link">Inicio</a>
            </nav>
        </header>

    <div class="container">
        <h1>Editar Juego</h1>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php elseif ($mensaje): ?>
            <div class="message success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="form-group">
                <label for="nombre">Nombre del juego:</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($juego['nombre']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($juego['descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="url">URL del juego:</label>
                <input type="url" id="url" name="url" value="<?php echo htmlspecialchars($juego['url']); ?>" required>
            </div>
            <div class="form-group">
                <label for="imagen">Imagen actual:</label>
                <img id="preview-img" class="img-preview" src="<?php echo htmlspecialchars($juego['imagen']); ?>" alt="Imagen actual">
                <input type="file" id="imagen" name="imagen" accept="image/*" onchange="previewImage(event)">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($juego['imagen']); ?>">
            </div>
            <button type="submit" class="btn">Guardar Cambios</button> <br> <br>
            <a href="ver_juegos.php" class="back-link">
                <i class="fas fa-arrow-left"></i> ← Volver a la zona de Juegos
            </a>
        </form>
    </div>
        <footer class="footer">
            <p>&copy; 2025 SABERQUEST - Todos los derechos reservados</p>
        </footer>
    <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('preview-img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
