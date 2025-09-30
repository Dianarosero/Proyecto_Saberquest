<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}
$formulario_id = $_GET['id'] ?? 0;

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion, imagen, mostrar_respuestas FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion, $imagen, $mostrar_respuestas);
$stmt->fetch();
$stmt->close();

// Validar existencia real del formulario (en guardar_formulario.php el título es obligatorio)
if (!empty($titulo)) {
    $form = [
        'titulo' => $titulo,
        'descripcion' => $descripcion,
        'background_image' => $imagen,
        'mostrar_respuestas' => $mostrar_respuestas,
    ];
} else {
    $form = null;
}

// Paginación
$preguntasPorPagina = 8;
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($paginaActual - 1) * $preguntasPorPagina;

// Obtener el total de preguntas
$totalPreguntasStmt = $conex->prepare("SELECT COUNT(*) FROM preguntas WHERE formulario_id = ?");
$totalPreguntasStmt->bind_param("i", $formulario_id);
$totalPreguntasStmt->execute();
$totalPreguntasStmt->bind_result($totalPreguntas);
$totalPreguntasStmt->fetch();
$totalPreguntasStmt->close();

$totalPaginas = ceil($totalPreguntas / $preguntasPorPagina);

// Obtener preguntas (agregando id) con paginación
$stmt = $conex->prepare("SELECT id, enunciado, imagen, opciones, correcta FROM preguntas WHERE formulario_id = ? LIMIT ?, ?");
$stmt->bind_param("iii", $formulario_id, $inicio, $preguntasPorPagina);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($titulo); ?> - SABERQUEST</title>
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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

    .contenedor {
        max-width: 800px;
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
        background-color: #B22222;
        border-radius: 3px;
    }

    .form-description {
        color: var(--text-light);
        font-size: 1.1rem;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
    }

    .preguntas-container {
        display: flex;
        flex-direction: column;
        gap: var(--gap);
    }

    .pregunta {
        background: var(--neutral-light);
        padding: 25px;
        border-radius: var(--border-radius);
        border-left: 4px solid var(--primary);
        margin-bottom: 5px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .pregunta:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-3px);
    }

    .pregunta-numero {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        margin-right: 0.5rem;
    }

    .pregunta-enunciado {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1.2rem;
        line-height: 1.4;
        display: flex;
        align-items: flex-start;
    }

    /* Opciones mejoradas para vista previa */
    .opciones-container {
        display: grid;
        gap: 18px;
        margin-top: 24px;
    }

    .opcion {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 18px 20px;
        border-radius: 12px;
        background: var(--background);
        border: 1.5px solid var(--neutral);
        transition: var(--transition);
        cursor: default;
        position: relative;
        overflow: hidden;
        min-height: 110px;
        box-shadow: var(--shadow-sm);
    }

    .opcion-letra {
        font-weight: 700;
        margin-bottom: 10px;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--neutral);
        border-radius: 50%;
        color: var(--text);
        font-size: 1.1rem;
    }

    .opcion-texto {
        width: 100%;
        font-size: 1.08rem;
        font-weight: 500;
        margin-bottom: 10px;
        color: var(--primary);
        text-align: left;
        word-break: break-word;
    }

    .opcion-imagen {
        width: 400px;
        max-width: 100%;
        max-height: 350px;
        object-fit: contain;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.10);
        margin: 10px auto 0 auto;
        display: block;
    }

    .correcta {
        background-color: rgba(39, 174, 96, 0.12);
        border-color: var(--success);
    }

    .correcta .opcion-letra {
        background-color: var(--success);
        color: white;
    }

    .correcta::after {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 18px;
        top: 18px;
        color: var(--success);
        font-size: 1.3rem;
    }

    hr {
        border: 0;
        height: 1px;
        background-color: var(--neutral);
        margin: 25px 0;
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

    .not-found {
        text-align: center;
        padding: 3rem 1rem;
    }

    .not-found h2 {
        display: inline-block;
        margin-bottom: 1.5rem;
    }

    .not-found i {
        font-size: 4rem;
        color: var(--secondary);
        margin-bottom: 1.5rem;
    }

    .not-found .btn {
        margin-top: 1.5rem;
    }

    /* Estilos de paginación */
    .paginacion {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .paginacion a,
    .paginacion span {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--background);
        border: 1px solid var(--neutral);
        color: var(--text);
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
    }

    .paginacion a:hover {
        background: var(--primary-light);
        color: white;
        border-color: var(--primary-light);
        transform: translateY(-2px);
    }

    .paginacion span.pagina-actual {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .paginacion .nav-anterior,
    .paginacion .nav-siguiente {
        width: auto;
        padding: 0 15px;
        border-radius: 18px;
        gap: 5px;
    }

    /* Botones de acción */
    .acciones-pregunta {
        position: absolute;
        top: 15px;
        right: 15px;
        display: flex;
        gap: 8px;
        opacity: 0;
        transition: var(--transition);
    }

    .pregunta:hover .acciones-pregunta {
        opacity: 1;
    }

    .btn-accion {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--background);
        border: 1px solid var(--neutral);
        color: var(--text-light);
        transition: var(--transition);
        cursor: pointer;
    }

    .btn-accion:hover {
        transform: translateY(-2px);
    }

    .btn-eliminar {
        color: var(--secondary);
        border-color: rgba(178, 34, 34, 0.3);
    }

    .btn-eliminar:hover {
        background: var(--secondary);
        color: white;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.2);
    }

    /* Modal de confirmación */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal {
        background: white;
        padding: 25px;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-lg);
        max-width: 90%;
        width: 400px;
        transform: translateY(-20px);
        transition: all 0.3s ease;
    }

    .modal-overlay.active .modal {
        transform: translateY(0);
    }

    .modal-header {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        gap: 10px;
    }

    .modal-header i {
        color: var(--secondary);
        font-size: 1.5rem;
    }

    .modal-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text);
    }

    .modal-content {
        margin-bottom: 20px;
        color: var(--text-light);
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-secondary {
        background-color: var(--neutral);
        color: var(--text);
        border: 2px solid transparent;
    }

    .btn-secondary:hover {
        background-color: var(--neutral-light);
        border-color: var(--neutral);
    }

    .btn-danger {
        background-color: var(--secondary);
        color: white;
        border: 2px solid transparent;
    }

    .btn-danger:hover {
        background-color: var(--secondary-light);
        box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.2);
    }

    /* Estilos de alertas */
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
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
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

        .pregunta {
            padding: 20px;
        }

        .pregunta-enunciado {
            font-size: 1.1rem;
        }

        .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
    }

    /* Animation for options */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .opcion {
        animation: fadeIn 0.3s ease forwards;
        opacity: 0;
    }

    .opcion:nth-child(1) {
        animation-delay: 0.1s;
    }

    .opcion:nth-child(2) {
        animation-delay: 0.2s;
    }

    .opcion:nth-child(3) {
        animation-delay: 0.3s;
    }

    .opcion:nth-child(4) {
        animation-delay: 0.4s;
    }

    /* Carrusel de imágenes de la pregunta */
    .pregunta-imagen-unica {
        margin: 10px 0 5px 0;
    }

    .pregunta-imagen-unica img {
        max-width: 100%;
        width: 100%;
        max-height: 320px;
        height: auto;
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        object-fit: contain;
        background: #fff;
    }

    .carousel {
        position: relative;
        margin: 10px 0 5px 0;
        overflow: hidden;
        border-radius: 8px;
        background: #fff;
        box-shadow: var(--shadow-sm);
    }

    .carousel-track {
        display: flex;
        transition: transform 0.35s ease;
        will-change: transform;
    }

    .carousel-slide {
        min-width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .carousel-slide img {
        max-width: 100%;
        width: 100%;
        max-height: 360px;
        height: auto;
        object-fit: contain;
        background: #fff;
    }

    .carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid var(--neutral);
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
        z-index: 2;
    }

    .carousel-btn:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        transform: translateY(-50%) scale(1.05);
    }

    .carousel-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .carousel-btn.prev {
        left: 10px;
    }

    .carousel-btn.next {
        right: 10px;
    }

    .carousel-dots {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        bottom: 8px;
        display: flex;
        gap: 6px;
        z-index: 1;
    }

    .carousel-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.15);
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }

    .carousel-dot:hover {
        background: rgba(0, 0, 0, 0.3);
    }

    .carousel-dot.active {
        background: var(--primary);
    }

    .nav-controls {
        display: flex;
        align-items: center;
        justify-content: center;
        /* Centra el contenido */
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
        transition: color 0.3s ease;
    }

    .nav-link:hover {
        color: #FFFFFF;
        /* Color más brillante al hacer hover */
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background-color: #FFFFFF;
        /* Blanco, como en la imagen */
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }

    a {
        text-decoration: none;
        color: inherit;
    }

    .mostrar-respuestas {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mostrar-respuestas label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 1rem;
        color: var(--text);
        cursor: pointer;
    }

    .mostrar-respuestas input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
        /* Color del checkbox cuando está marcado */
    }

    .mostrar-respuestas input[type="checkbox"]:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    /* Evitar subrayado heredado en títulos de SweetAlert */
    .swal2-title::after {
        content: none !important;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    // Función para actualizar el estado de mostrar_respuestas
    function actualizarMostrarRespuestas(formulario_id) {
        var checkbox = document.getElementById('mostrar_resultados');
        var valor = checkbox.checked ? 1 : 0;

        // Crear una solicitud AJAX
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "actualizar_mostrar_respuestas.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        // Manejar la respuesta del servidor
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    console.log("Estado actualizado correctamente");
                } else {
                    console.error("Error al actualizar el estado: " + response.error);
                    // Si hay un error, revertir el estado del checkbox
                    checkbox.checked = !checkbox.checked;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al actualizar el estado. Por favor, intenta de nuevo.'
                        });
                    } else {
                        alert("Hubo un error al actualizar el estado. Por favor, intenta de nuevo.");
                    }
                }
            }
        };

        // Enviar los datos
        xhr.send("formulario_id=" + formulario_id + "&mostrar_respuestas=" + valor);
    }


    // Función para mostrar el modal de confirmación de eliminación del formulario
    function confirmarEliminarFormulario(id, titulo) {
        document.getElementById('formulario_id_eliminar').value = id;
        document.getElementById('formulario-titulo-eliminar').textContent = titulo;
        document.getElementById('modal-eliminar-formulario').classList.add('active');
    }

    // Función para cerrar el modal
    function cerrarModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // Cerrar modales al hacer clic fuera de ellos
    window.addEventListener('click', function(event) {
        var modalFormulario = document.getElementById('modal-eliminar-formulario');
        if (event.target === modalFormulario) {
            cerrarModal('modal-eliminar-formulario');
        }
    });
    </script>
</head>

<body>
    <?php if ($form): ?>
    <?php
        // Usar la imagen de la base de datos o la imagen predeterminada
        $default_image = '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada2.png';
        $bg_image = !empty($form['background_image']) ? $form['background_image'] : $default_image;
        ?>
    <div class="bg-container" style="background-image: url('<?php echo htmlspecialchars($bg_image); ?>')"></div>

    <header class="header">

        <div class="logo-space">
            <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
        </div>

        <div class="nav-controls">
            <nav class="nav">
                <div class="nav-list">
                    <a class="nav-link" href="../index_admin.php#projects">Inicio</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="contenedor">
        <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] == 'eliminado'): ?>
        <div class="alerta alerta-exito">
            <i class="fas fa-check-circle"></i>
            <span>La pregunta ha sido eliminada correctamente.</span>
            <button type="button" class="cerrar-alerta" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'eliminar'): ?>
        <div class="alerta alerta-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>Hubo un error al intentar eliminar la pregunta. Por favor, inténtalo de nuevo.</span>
            <button type="button" class="cerrar-alerta" onclick="this.parentElement.style.display='none';">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <div class="form-header">
            <div class="title-actions">
                <h2><?php echo htmlspecialchars($form['titulo']); ?></h2>
                <div class="form-actions">
                    <a href="editar_formulario.php?id=<?php echo $formulario_id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button type="button" class="btn btn-danger"
                        onclick="confirmarEliminarFormulario(<?php echo $formulario_id; ?>, '<?php echo htmlspecialchars(addslashes($form['titulo'])); ?>')">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </div>
            </div>
            <p class="form-description"><?php echo nl2br(htmlspecialchars($form['descripcion'])); ?></p>
            <div class="mostrar-respuestas">
                <label for="mostrar_resultados">
                    <input type="checkbox" id="mostrar_resultados" name="mostrar_resultados" value="1"
                        <?php echo $form['mostrar_respuestas'] == 1 ? 'checked' : ''; ?>
                        onchange="actualizarMostrarRespuestas(<?php echo $formulario_id; ?>)">
                    Mostrar resultados estudiante
                </label>
            </div>
        </div>

        <div class="preguntas-container">
            <?php
                $num = 1;
                while ($row = $result->fetch_assoc()):
                    // Estructura de opciones: { a: { texto, imagen }, b: {...}, c: {...}, d: {...} }
                    $opciones = json_decode($row['opciones'], true);
                    if (!is_array($opciones)) { $opciones = []; }
                    $correcta = $row['correcta'];

                    // Estructura de imágenes de pregunta: array JSON o string (legacy)
                    $imgsPregunta = json_decode($row['imagen'], true);
                    $imgsArray = is_array($imgsPregunta) ? $imgsPregunta : [];
                    if (!$imgsArray && !empty($row['imagen']) && is_string($row['imagen'])) {
                        // Compatibilidad con registros antiguos (una sola imagen en texto)
                        $imgsArray = [$row['imagen']];
                    }
                ?>
            <div class="pregunta">
                <div class="pregunta-enunciado">
                    <span
                        class="pregunta-numero"><?php echo ($paginaActual - 1) * $preguntasPorPagina + $num++; ?>.</span>
                    <span><?php echo htmlspecialchars($row['enunciado']); ?></span>
                </div>

                <?php
                    if (!empty($imgsArray)) {
                        // Filtrar imágenes válidas (no vacías)
                        $validImgs = array_values(array_filter($imgsArray, function($src) {
                            return isset($src) && strlen(trim((string)$src)) > 0;
                        }));
                        $imgCount = count($validImgs);
                        if ($imgCount === 1) {
                            $imgSrc = $validImgs[0];
                ?>
                <div class="pregunta-imagen-unica">
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Imagen de la pregunta">
                </div>
                <?php
                        } elseif ($imgCount > 1) {
                            // Carrusel cuando hay más de una imagen
                ?>
                <div class="carousel" data-current="0">
                    <button class="carousel-btn prev" type="button" aria-label="Anterior">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <div class="carousel-track">
                        <?php foreach ($validImgs as $imgSrc): ?>
                        <div class="carousel-slide">
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Imagen de la pregunta">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-btn next" type="button" aria-label="Siguiente">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="carousel-dots">
                        <?php for ($i = 0; $i < $imgCount; $i++): ?>
                        <button class="carousel-dot<?php echo $i === 0 ? ' active' : ''; ?>" type="button"
                            data-index="<?php echo $i; ?>" aria-label="Ir a la imagen <?php echo $i + 1; ?>"></button>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php
                        }
                    }
                ?>

                <div class="opciones-container">
                    <?php
                            $letras = ['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'];
                            foreach ($letras as $key => $label):
                                // Si tus opciones vienen como array con texto e imagen:
                                $op = $opciones[$key] ?? null;
                                $texto = is_array($op) ? ($op['texto'] ?? '') : (is_string($op) ? $op : '');
                                $imagen = is_array($op) ? ($op['imagen'] ?? '') : '';
                            ?>
                    <div class="opcion<?php if ($correcta === $key) echo ' correcta'; ?>">
                        <span class="opcion-letra"><?php echo $label; ?></span>
                        <?php if (strlen(trim((string)$texto)) > 0): ?>
                            <span class="opcion-texto"><?php echo htmlspecialchars($texto); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($imagen)): ?>
                            <img src="<?php echo htmlspecialchars($imagen); ?>" alt="Opción <?php echo $label; ?>" class="opcion-imagen">
                        <?php endif; ?>
                        <?php if (empty($imagen) && strlen(trim((string)$texto)) === 0): ?>
                            <span style="color:var(--text-light);font-style:italic;">(Sin contenido)</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($totalPaginas > 1): ?>
        <div class="paginacion">
            <?php if ($paginaActual > 1): ?>
            <a href="?id=<?php echo $formulario_id; ?>&pagina=<?php echo $paginaActual - 1; ?>" class="nav-anterior">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
            <?php endif; ?>

            <?php
                    // Mostrar números de página con limitación
                    $startPage = max(1, $paginaActual - 2);
                    $endPage = min($startPage + 4, $totalPaginas);

                    if ($startPage > 1) {
                        echo '<a href="?id=' . $formulario_id . '&pagina=1">1</a>';
                        if ($startPage > 2) {
                            echo '<span>...</span>';
                        }
                    }

                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $paginaActual) {
                            echo '<span class="pagina-actual">' . $i . '</span>';
                        } else {
                            echo '<a href="?id=' . $formulario_id . '&pagina=' . $i . '">' . $i . '</a>';
                        }
                    }

                    if ($endPage < $totalPaginas) {
                        if ($endPage < $totalPaginas - 1) {
                            echo '<span>...</span>';
                        }
                        echo '<a href="?id=' . $formulario_id . '&pagina=' . $totalPaginas . '">' . $totalPaginas . '</a>';
                    }
                    ?>

            <?php if ($paginaActual < $totalPaginas): ?>
            <a href="?id=<?php echo $formulario_id; ?>&pagina=<?php echo $paginaActual + 1; ?>" class="nav-siguiente">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <a href="formularios.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver a la lista de simulacros
        </a>
    </div>

    <!-- Modal de confirmación para eliminar formulario -->
    <div id="modal-eliminar-formulario" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h3 class="modal-title">Confirmar eliminación</h3>
            </div>
            <div class="modal-content">
                <p>¿Estás seguro de que deseas eliminar el simulacro: <strong id="formulario-titulo-eliminar"></strong>?
                </p>
                <p>Esta acción eliminará el simulacro y todas sus preguntas. No se puede deshacer.</p>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="cerrarModal('modal-eliminar-formulario')">Cancelar</button>
                <form id="form-eliminar-formulario" method="post" action="eliminar_formulario2.php"
                    style="display:inline;">
                    <input type="hidden" id="formulario_id_eliminar" name="formulario_id" value="">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST - Todos los derechos reservados</p>
    </footer>

    <?php else: ?>
    <div class="contenedor not-found">
        <i class="fas fa-exclamation-triangle"></i>
        <h2>Simulacro no encontrado</h2>
        <p>Lo sentimos, el simulacro solicitado no existe o ha sido eliminado.</p>
        <a href="../index_admin.php#projects" class="btn btn-primary">
            <i class="fas fa-home"></i> Volver al inicio
        </a>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST - Todos los derechos reservados</p>
    </footer>
    <?php endif; ?>
</body>

<script>
// Inicialización ligera de carruseles por pregunta
document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(function(carousel) {
        const track = carousel.querySelector('.carousel-track');
        if (!track) return;
        const slides = Array.from(track.querySelectorAll('.carousel-slide'));
        const prevBtn = carousel.querySelector('.carousel-btn.prev');
        const nextBtn = carousel.querySelector('.carousel-btn.next');
        const dots = Array.from(carousel.querySelectorAll('.carousel-dot'));

        let index = 0;

        function update() {
            track.style.transform = 'translateX(' + (-index * 100) + '%)';
            if (prevBtn) prevBtn.disabled = index === 0;
            if (nextBtn) nextBtn.disabled = index === slides.length - 1;
            if (dots.length) {
                dots.forEach((d, i) => d.classList.toggle('active', i === index));
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', function() {
            if (index > 0) {
                index--;
                update();
            }
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
            if (index < slides.length - 1) {
                index++;
                update();
            }
        });
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                const i = parseInt(dot.getAttribute('data-index'), 10);
                if (!isNaN(i)) {
                    index = i;
                    update();
                }
            });
        });

        // Soporte básico de arrastre táctil
        let startX = 0,
            deltaX = 0,
            isDragging = false;
        track.addEventListener('touchstart', function(e) {
            isDragging = true;
            startX = e.touches[0].clientX;
            deltaX = 0;
        }, {
            passive: true
        });
        track.addEventListener('touchmove', function(e) {
            if (!isDragging) return;
            deltaX = e.touches[0].clientX - startX;
        }, {
            passive: true
        });
        track.addEventListener('touchend', function() {
            if (!isDragging) return;
            isDragging = false;
            const threshold = 50; // px
            if (deltaX > threshold && index > 0) {
                index--;
            } else if (deltaX < -threshold && index < slides.length - 1) {
                index++;
            }
            update();
        });

        update();
    });
});
</script>

</html>