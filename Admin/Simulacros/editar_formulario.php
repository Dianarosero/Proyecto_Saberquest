<?php
include("../../base de datos/con_db.php");
$formulario_id = $_GET['id'] ?? 0;
$mensaje = '';
$error = '';

// Si no hay ID, redirigir al listado
if ($formulario_id == 0) {
    header("Location: index.php");
    exit;
}

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion, imagen FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion, $imagen);
$formulario_encontrado = $stmt->fetch();
$stmt->close();

if (!$formulario_encontrado) {
    header("Location: index.php?error=formulario_no_encontrado");
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_nuevo = $_POST['titulo'] ?? '';
    $descripcion_nueva = $_POST['descripcion'] ?? '';
    $imagen_ruta = $imagen; // Mantener la imagen actual por defecto
    
    // Procesar la imagen si se ha subido una nueva
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $carpeta_destino = "../../assets/src_simulacros/img_simulacros/";
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
    if (empty($titulo_nuevo)) {
        $error = "El título del formulario no puede estar vacío.";
    } else if (empty($error)) { // Solo proceder si no hay errores
        // Actualizar formulario
        $stmt_update = $conex->prepare("UPDATE formularios SET titulo = ?, descripcion = ?, imagen = ? WHERE id = ?");
        $stmt_update->bind_param("sssi", $titulo_nuevo, $descripcion_nueva, $imagen_ruta, $formulario_id);
        
        if ($stmt_update->execute()) {
            $mensaje = "Formulario actualizado correctamente.";
            $titulo = $titulo_nuevo;
            $descripcion = $descripcion_nueva;
            $imagen = $imagen_ruta;
        } else {
            $error = "Error al actualizar el formulario: " . $conex->error;
        }
        
        $stmt_update->close();
    }
}

// Obtener las preguntas del formulario
$preguntas = [];
$stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ?");
$stmt_preguntas->bind_param("i", $formulario_id);
$stmt_preguntas->execute();
$result_preguntas = $stmt_preguntas->get_result();

while ($pregunta = $result_preguntas->fetch_assoc()) {
    $preguntas[] = $pregunta;
}
$stmt_preguntas->close();

// Procesar edición de preguntas
if (isset($_POST['editar_pregunta'])) {
    $pregunta_id = $_POST['pregunta_id'] ?? 0;
    $enunciado = $_POST['enunciado'] ?? '';
    $opcion_a = $_POST['opcion_a'] ?? '';
    $opcion_b = $_POST['opcion_b'] ?? '';
    $opcion_c = $_POST['opcion_c'] ?? '';
    $opcion_d = $_POST['opcion_d'] ?? '';
    $correcta = $_POST['correcta'] ?? '';
    
    if (empty($enunciado) || empty($opcion_a) || empty($opcion_b) || empty($opcion_c) || empty($opcion_d) || empty($correcta)) {
        $error = "Todos los campos de la pregunta son obligatorios.";
    } else {
        $opciones = json_encode([
            'a' => $opcion_a,
            'b' => $opcion_b,
            'c' => $opcion_c,
            'd' => $opcion_d
        ]);
        
        $stmt_update_pregunta = $conex->prepare("UPDATE preguntas SET enunciado = ?, opciones = ?, correcta = ? WHERE id = ? AND formulario_id = ?");
        $stmt_update_pregunta->bind_param("sssii", $enunciado, $opciones, $correcta, $pregunta_id, $formulario_id);
        
        if ($stmt_update_pregunta->execute()) {
            $mensaje = "Pregunta actualizada correctamente.";
            
            // Actualizar la lista de preguntas
            $stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ?");
            $stmt_preguntas->bind_param("i", $formulario_id);
            $stmt_preguntas->execute();
            $result_preguntas = $stmt_preguntas->get_result();
            
            $preguntas = [];
            while ($pregunta = $result_preguntas->fetch_assoc()) {
                $preguntas[] = $pregunta;
            }
            $stmt_preguntas->close();
        } else {
            $error = "Error al actualizar la pregunta: " . $conex->error;
        }
        
        $stmt_update_pregunta->close();
    }
}

// Procesar nueva pregunta
if (isset($_POST['agregar_pregunta'])) {
    $enunciado = $_POST['nuevo_enunciado'] ?? '';
    $opcion_a = $_POST['nueva_opcion_a'] ?? '';
    $opcion_b = $_POST['nueva_opcion_b'] ?? '';
    $opcion_c = $_POST['nueva_opcion_c'] ?? '';
    $opcion_d = $_POST['nueva_opcion_d'] ?? '';
    $correcta = $_POST['nueva_correcta'] ?? '';
    
    if (empty($enunciado) || empty($opcion_a) || empty($opcion_b) || empty($opcion_c) || empty($opcion_d) || empty($correcta)) {
        $error = "Todos los campos de la nueva pregunta son obligatorios.";
    } else {
        $opciones = json_encode([
            'a' => $opcion_a,
            'b' => $opcion_b,
            'c' => $opcion_c,
            'd' => $opcion_d
        ]);
        
        $stmt_insert_pregunta = $conex->prepare("INSERT INTO preguntas (formulario_id, enunciado, opciones, correcta) VALUES (?, ?, ?, ?)");
        $stmt_insert_pregunta->bind_param("isss", $formulario_id, $enunciado, $opciones, $correcta);
        
        if ($stmt_insert_pregunta->execute()) {
            $mensaje = "Pregunta agregada correctamente.";
            
            // Actualizar la lista de preguntas
            $stmt_preguntas = $conex->prepare("SELECT id, enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ?");
            $stmt_preguntas->bind_param("i", $formulario_id);
            $stmt_preguntas->execute();
            $result_preguntas = $stmt_preguntas->get_result();
            
            $preguntas = [];
            while ($pregunta = $result_preguntas->fetch_assoc()) {
                $preguntas[] = $pregunta;
            }
            $stmt_preguntas->close();
        } else {
            $error = "Error al agregar la pregunta: " . $conex->error;
        }
        
        $stmt_insert_pregunta->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Simulacro</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #003366;
            --primary-light: #0056b3;
            --secondary: #B22222;
            --secondary-light: #d93636;
            --accent: #FFD700;
            --accent-light: #FFE44D;
            --background: #FFFFFF;
            --text: #333333;
            --text-light: #666666;
            --neutral: #E0E0E0;
            --neutral-light: #F7F7F7;
            --success: #27ae60;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
            --gap: 1.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--neutral-light);
            color: var(--text);
            position: relative;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            line-height: 1.6;
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
            padding: 1.2rem 12rem;
            width: 100%;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: var(--transition);
        }

        .header:hover {
            box-shadow: var(--shadow-lg);
        }

        .university-logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .university-logo i {
            margin-right: 10px;
            color: var(--accent);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: 2px solid transparent;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.2);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success);
            color: white;
            border: 2px solid transparent;
        }

        .btn-success:hover {
            background-color: #219653;
            box-shadow: 0 0 0 3px rgba(39, 174, 96, 0.2);
            transform: translateY(-2px);
        }

        .contenedor {
            max-width: 900px;
            width: 100%;
            margin: 30px auto;
            background: var(--background);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            flex: 1;
        }

        .contenedor:hover {
            box-shadow: var(--shadow-lg);
        }

        .form-header {
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--neutral);
            padding-bottom: 1.5rem;
        }
        
        .title-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            gap: 15px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        h2 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40%;
            height: 3px;
            background-color: var(--accent);
            border-radius: 3px;
        }

        .form-description {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 1px solid var(--neutral);
            border-radius: 8px;
            transition: var(--transition);
            font-family: 'Montserrat', sans-serif;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(0, 51, 102, 0.1);
        }

        .form-control-file {
            padding: 10px 0;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .image-upload-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .current-image {
            padding: 15px;
            border: 1px solid var(--neutral);
            border-radius: var(--border-radius);
            background-color: var(--neutral-light);
        }
        
        .current-image p {
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-light);
        }
        
        .img-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
        }
        
        .file-input-wrapper {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .accordion {
            margin-top: 2rem;
            border: 1px solid var(--neutral);
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .accordion-item {
            border-bottom: 1px solid var(--neutral);
        }

        .accordion-item:last-child {
            border-bottom: none;
        }

        .accordion-header {
            background: var(--neutral-light);
            padding: 15px 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            transition: var(--transition);
        }

        .accordion-header:hover {
            background-color: #e9e9e9;
        }

        .accordion-header.active {
            background-color: var(--primary);
            color: white;
        }

        .accordion-body {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .accordion-body.active {
            padding: 20px;
            max-height: 1000px;
        }

        .option-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .radio-option input[type="radio"] {
            margin: 0;
        }

        .section-title {
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--neutral);
        }

        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .form-actions-bottom {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 2.5rem;
            margin-bottom: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--neutral);
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

        .back-link:hover {
            color: var(--primary-light);
            transform: translateX(-5px);
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

        .alerta {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            position: relative;
            animation: slideDown 0.3s ease-out forwards;
        }
        
        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .alerta i:first-child {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .alerta-exito {
            background-color: rgba(39, 174, 96, 0.1);
            border: 1px solid var(--success);
            color: var(--success);
        }
        
        .alerta-error {
            background-color: rgba(178, 34, 34, 0.1);
            border: 1px solid var(--secondary);
            color: var(--secondary);
        }
        
        .cerrar-alerta {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: var(--transition);
        }
        
        .cerrar-alerta:hover {
            opacity: 1;
        }

        .card {
            background: var(--neutral-light);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-md);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--background);
            border: 1px solid var(--neutral);
            color: var(--text-light);
            transition: var(--transition);
            cursor: pointer;
        }

        .btn-icon:hover {
            transform: translateY(-2px);
        }

        .btn-icon.edit {
            color: var(--primary);
            border-color: rgba(0, 51, 102, 0.3);
        }

        .btn-icon.edit:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-icon.delete {
            color: var(--secondary);
            border-color: rgba(178, 34, 34, 0.3);
        }

        .btn-icon.delete:hover {
            background: var(--secondary);
            color: white;
            border-color: var(--secondary);
        }

        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }
            
            .university-logo {
                font-size: 1.2rem;
            }
            
            .contenedor {
                margin: 15px;
                padding: 25px;
                border-radius: 10px;
            }
            
            h2 {
                font-size: 1.6rem;
            }
            
            .option-grid {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .actions .btn {
                width: 100%;
            }
        }
    </style>

    <script>
        // JavaScript para manejar los acordeones
        document.addEventListener('DOMContentLoaded', function() {
            // Función para manejar la apertura/cierre de los acordeones
            function handleAccordion() {
                const headers = document.querySelectorAll('.accordion-header');
                
                headers.forEach(header => {
                    header.addEventListener('click', function() {
                        this.classList.toggle('active');
                        const body = this.nextElementSibling;
                        body.classList.toggle('active');
                    });
                });
            }
            
            // Función para cerrar las alertas
            function setupAlertClosing() {
                const closeButtons = document.querySelectorAll('.cerrar-alerta');
                
                closeButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        this.parentElement.style.display = 'none';
                    });
                });
            }
            
            // Inicializar las funciones
            handleAccordion();
            setupAlertClosing();
        });
    </script>
</head>

<body>
    <div class="bg-container"></div>

    <header class="header">
    <div class="logo-space">
                <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
            </div>
        <div class="mode-toggle">
            <a href="../index_admin.php" class="btn btn-primary">
                Inicio
            </a>
        </div>
    </header>

    <div class="contenedor">
        <?php if (!empty($mensaje)): ?>
            <div class="alerta alerta-exito">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $mensaje; ?></span>
                <button type="button" class="cerrar-alerta">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
                <button type="button" class="cerrar-alerta">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <div class="form-header">
            <div class="title-actions">
                <h2>Editar Simulacro</h2>
                <div class="form-actions">
                    <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Ver Simulacro
                    </a>
                </div>
            </div>
            <p class="form-description">Modifica el título, descripción, imagen y las preguntas del simulacro.</p>
        </div>

        <!-- Formulario para editar los datos generales -->
        <form method="post" action="" id="form-datos-generales" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título del simulacro</label>
                <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo htmlspecialchars($titulo); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($descripcion); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="imagen">Imagen</label>
                <div class="image-upload-container">
                    <?php if (!empty($imagen)): ?>
                        <div class="current-image">
                            <p>Imagen actual:</p>
                            <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Imagen actual" class="img-preview">
                        </div>
                    <?php endif; ?>
                    <div class="file-input-wrapper">
                        <input type="file" class="form-control-file" id="imagen" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Selecciona una imagen para usarla como fondo del simulacro. Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP.</small>
                    </div>
                </div>
            </div>

        <h3 class="section-title">Preguntas del simulacro</h3>
        
        <!-- Lista de preguntas existentes -->
        <div class="accordion">
            <?php foreach ($preguntas as $index => $pregunta): 
                $opciones = json_decode($pregunta['opciones'], true);
            ?>
                <div class="accordion-item">
                    <div class="accordion-header">
                        <span><?php echo ($index + 1) . '. ' . htmlspecialchars(substr($pregunta['enunciado'], 0, 60)) . (strlen($pregunta['enunciado']) > 60 ? '...' : ''); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-body">
                        <form method="post" action="">
                            <input type="hidden" name="pregunta_id" value="<?php echo $pregunta['id']; ?>">
                            
                            <div class="form-group">
                                <label for="enunciado_<?php echo $pregunta['id']; ?>">Enunciado de la pregunta</label>
                                <textarea class="form-control" id="enunciado_<?php echo $pregunta['id']; ?>" name="enunciado" rows="2" required><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
                            </div>
                            
                            <div class="option-grid">
                                <div class="form-group">
                                    <label for="opcion_a_<?php echo $pregunta['id']; ?>">Opción A</label>
                                    <input type="text" class="form-control" id="opcion_a_<?php echo $pregunta['id']; ?>" name="opcion_a" value="<?php echo htmlspecialchars($opciones['a']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="opcion_b_<?php echo $pregunta['id']; ?>">Opción B</label>
                                    <input type="text" class="form-control" id="opcion_b_<?php echo $pregunta['id']; ?>" name="opcion_b" value="<?php echo htmlspecialchars($opciones['b']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="opcion_c_<?php echo $pregunta['id']; ?>">Opción C</label>
                                    <input type="text" class="form-control" id="opcion_c_<?php echo $pregunta['id']; ?>" name="opcion_c" value="<?php echo htmlspecialchars($opciones['c']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="opcion_d_<?php echo $pregunta['id']; ?>">Opción D</label>
                                    <input type="text" class="form-control" id="opcion_d_<?php echo $pregunta['id']; ?>" name="opcion_d" value="<?php echo htmlspecialchars($opciones['d']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Respuesta correcta</label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="correcta_a_<?php echo $pregunta['id']; ?>" name="correcta" value="a" <?php if ($pregunta['correcta'] == 'a') echo 'checked'; ?> required>
                                        <label for="correcta_a_<?php echo $pregunta['id']; ?>">A</label>
                                    </div>
                                    
                                    <div class="radio-option">
                                        <input type="radio" id="correcta_b_<?php echo $pregunta['id']; ?>" name="correcta" value="b" <?php if ($pregunta['correcta'] == 'b') echo 'checked'; ?>>
                                        <label for="correcta_b_<?php echo $pregunta['id']; ?>">B</label>
                                    </div>
                                    
                                    <div class="radio-option">
                                        <input type="radio" id="correcta_c_<?php echo $pregunta['id']; ?>" name="correcta" value="c" <?php if ($pregunta['correcta'] == 'c') echo 'checked'; ?>>
                                        <label for="correcta_c_<?php echo $pregunta['id']; ?>">C</label>
                                    </div>
                                    
                                    <div class="radio-option">
                                        <input type="radio" id="correcta_d_<?php echo $pregunta['id']; ?>" name="correcta" value="d" <?php if ($pregunta['correcta'] == 'd') echo 'checked'; ?>>
                                        <label for="correcta_d_<?php echo $pregunta['id']; ?>">D</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="actions">
                                <button type="submit" name="editar_pregunta" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Pregunta
                                </button>
                                
                                <a href="eliminar_pregunta.php?id=<?php echo $pregunta['id']; ?>&formulario_id=<?php echo $formulario_id; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas eliminar esta pregunta?');">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Formulario para agregar nueva pregunta -->
        <h3 class="section-title">Añadir nueva pregunta</h3>
        <div class="card">
            <form method="post" action="">
                <div class="form-group">
                    <label for="nuevo_enunciado">Enunciado de la pregunta</label>
                    <textarea class="form-control" id="nuevo_enunciado" name="nuevo_enunciado" rows="2" required></textarea>
                </div>
                
                <div class="option-grid">
                    <div class="form-group">
                        <label for="nueva_opcion_a">Opción A</label>
                        <input type="text" class="form-control" id="nueva_opcion_a" name="nueva_opcion_a" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nueva_opcion_b">Opción B</label>
                        <input type="text" class="form-control" id="nueva_opcion_b" name="nueva_opcion_b" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nueva_opcion_c">Opción C</label>
                        <input type="text" class="form-control" id="nueva_opcion_c" name="nueva_opcion_c" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nueva_opcion_d">Opción D</label>
                        <input type="text" class="form-control" id="nueva_opcion_d" name="nueva_opcion_d" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Respuesta correcta</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" id="nueva_correcta_a" name="nueva_correcta" value="a" required>
                            <label for="nueva_correcta_a">A</label>
                        </div>
                        
                        <div class="radio-option">
                            <input type="radio" id="nueva_correcta_b" name="nueva_correcta" value="b">
                            <label for="nueva_correcta_b">B</label>
                        </div>
                        
                        <div class="radio-option">
                            <input type="radio" id="nueva_correcta_c" name="nueva_correcta" value="c">
                            <label for="nueva_correcta_c">C</label>
                        </div>
                        
                        <div class="radio-option">
                            <input type="radio" id="nueva_correcta_d" name="nueva_correcta" value="d">
                            <label for="nueva_correcta_d">D</label>
                        </div>
                    </div>
                </div>
                
                <div class="actions">
                    <button type="submit" name="agregar_pregunta" class="btn btn-success">
                        <i class="fas fa-plus"></i> Añadir Pregunta
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Botones de guardar cambios y cancelar -->
        <div class="form-actions-bottom">
            <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="btn btn-outline">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" form="form-datos-generales" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
        </div>

        <a href="ver_formulario.php?id=<?php echo $formulario_id; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al simulacro
        </a>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST - Todos los derechos reservados</p>
    </footer>
</body>

</html>