<?php
include("../../base de datos/con_db.php");
$formulario_id = $_GET['id'] ?? 0;

// Obtener datos del formulario
$stmt = $conex->prepare("SELECT titulo, descripcion, imagen FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion, $imagen);
$stmt->fetch();
$stmt->close();

$form = [
    'titulo' => $titulo,
    'descripcion' => $descripcion,
    'background_image' => $imagen,
];

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
$stmt = $conex->prepare("SELECT id, enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ? LIMIT ?, ?");
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

        .opciones-container {
            display: grid;
            gap: 12px;
            margin-top: 20px;
        }

        .opcion {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            background: var(--background);
            border: 1px solid var(--neutral);
            transition: var(--transition);
            cursor: default;
            position: relative;
            overflow: hidden;
        }

        .opcion:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow-sm);
        }

        .opcion-letra {
            font-weight: 700;
            margin-right: 10px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--neutral);
            border-radius: 50%;
            color: var(--text);
            transition: var(--transition);
        }

        .opcion-texto {
            flex: 1;
            font-size: 1rem;
        }

        .correcta {
            background-color: rgba(39, 174, 96, 0.1);
            border-color: var(--success);
        }

        .correcta .opcion-letra {
            background-color: var(--success);
            color: white;
        }

        .correcta::after {
            content: '\f00c'; /* fa-check */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: var(--success);
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
        
        .paginacion a, .paginacion span {
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .opcion {
            animation: fadeIn 0.3s ease forwards;
            opacity: 0;
        }

        .opcion:nth-child(1) { animation-delay: 0.1s; }
        .opcion:nth-child(2) { animation-delay: 0.2s; }
        .opcion:nth-child(3) { animation-delay: 0.3s; }
        .opcion:nth-child(4) { animation-delay: 0.4s; }
    </style>
    
    <script>
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
        $default_image = 'https://pixabay.com/get/g8386919d873394672d9c4f2b4a58bfdf6ddbc88918bd7be5af792f69144340e15c8134ca7a5df3c89411ba0b9f15bc66048659caff8143cbeee94118364b59da_1280.jpg';
        $bg_image = !empty($form['background_image']) ? $form['background_image'] : $default_image;
        ?>
        <div class="bg-container" style="background-image: url('<?php echo htmlspecialchars($bg_image); ?>')"></div>

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
                        <button type="button" class="btn btn-danger" onclick="confirmarEliminarFormulario(<?php echo $formulario_id; ?>, '<?php echo htmlspecialchars(addslashes($form['titulo'])); ?>')">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </button>
                    </div>
                </div>
                <p class="form-description"><?php echo nl2br(htmlspecialchars($form['descripcion'])); ?></p>
            </div>

            <div class="preguntas-container">
                <?php
                $num = 1;
                while ($row = $result->fetch_assoc()):
                    $opciones = json_decode($row['opciones'], true);
                    $correcta = $row['correcta'];
                ?>
                    <div class="pregunta">
                        <div class="pregunta-enunciado">
                            <span class="pregunta-numero"><?php echo ($paginaActual - 1) * $preguntasPorPagina + $num++; ?>.</span>
                            <span><?php echo htmlspecialchars($row['enunciado']); ?></span>
                        </div>

                        <div class="opciones-container">
                            <div class="opcion<?php if ($correcta == 'a') echo ' correcta'; ?>">
                                <span class="opcion-letra">A</span>
                                <span class="opcion-texto"><?php echo htmlspecialchars($opciones['a']); ?></span>
                            </div>
                            <div class="opcion<?php if ($correcta == 'b') echo ' correcta'; ?>">
                                <span class="opcion-letra">B</span>
                                <span class="opcion-texto"><?php echo htmlspecialchars($opciones['b']); ?></span>
                            </div>
                            <div class="opcion<?php if ($correcta == 'c') echo ' correcta'; ?>">
                                <span class="opcion-letra">C</span>
                                <span class="opcion-texto"><?php echo htmlspecialchars($opciones['c']); ?></span>
                            </div>
                            <div class="opcion<?php if ($correcta == 'd') echo ' correcta'; ?>">
                                <span class="opcion-letra">D</span>
                                <span class="opcion-texto"><?php echo htmlspecialchars($opciones['d']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <div class="paginacion">
                    <?php if ($paginaActual > 1): ?>
                        <a href="?id=<?php echo $formulario_id; ?>&pagina=<?php echo $paginaActual-1; ?>" class="nav-anterior">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Mostrar números de página con limitación
                    $startPage = max(1, $paginaActual - 2);
                    $endPage = min($startPage + 4, $totalPaginas);
                    
                    if ($startPage > 1) {
                        echo '<a href="?id='.$formulario_id.'&pagina=1">1</a>';
                        if ($startPage > 2) {
                            echo '<span>...</span>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i == $paginaActual) {
                            echo '<span class="pagina-actual">'.$i.'</span>';
                        } else {
                            echo '<a href="?id='.$formulario_id.'&pagina='.$i.'">'.$i.'</a>';
                        }
                    }
                    
                    if ($endPage < $totalPaginas) {
                        if ($endPage < $totalPaginas - 1) {
                            echo '<span>...</span>';
                        }
                        echo '<a href="?id='.$formulario_id.'&pagina='.$totalPaginas.'">'.$totalPaginas.'</a>';
                    }
                    ?>
                    
                    <?php if ($paginaActual < $totalPaginas): ?>
                        <a href="?id=<?php echo $formulario_id; ?>&pagina=<?php echo $paginaActual+1; ?>" class="nav-siguiente">
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
                    <p>¿Estás seguro de que deseas eliminar el simulacro: <strong id="formulario-titulo-eliminar"></strong>?</p>
                    <p>Esta acción eliminará el simulacro y todas sus preguntas. No se puede deshacer.</p>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="cerrarModal('modal-eliminar-formulario')">Cancelar</button>
                    <form id="form-eliminar-formulario" method="post" action="eliminar_formulario2.php" style="display:inline;">
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
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Volver al inicio
            </a>
        </div>

        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> SABERQUEST - Todos los derechos reservados</p>
        </footer>
    <?php endif; ?>
</body>

</html>
