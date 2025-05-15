<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Docente') {
    header('Location: ../../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$formulario_id = $_GET['form_id'] ?? 0;
$estudiante_id = $_GET['user_id'] ?? 0;

// Verificar que el formulario existe y pertenece al profesor
$stmt = $conex->prepare("
    SELECT id, titulo, descripcion, imagen
    FROM formularios 
    WHERE id = ?
");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Formulario no encontrado o no tienes permiso para ver estos resultados.");
}

$formulario = $result->fetch_assoc();
$stmt->close();

// Obtener información del estudiante
$stmt = $conex->prepare("SELECT id, nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $estudiante_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Estudiante no encontrado.");
}

$estudiante = $result->fetch_assoc();
$stmt->close();

// Obtener estadísticas del estudiante
$stmt = $conex->prepare("
    SELECT 
        COUNT(DISTINCT p.id) as total_preguntas,
        SUM(CASE WHEN r.respuesta = p.correcta THEN 1 ELSE 0 END) as respuestas_correctas,
        MAX(r.fecha) as fecha_respuesta
    FROM preguntas p
    LEFT JOIN respuestas r ON p.id = r.pregunta_id AND r.usuario_id = ?
    WHERE p.formulario_id = ? AND (r.formulario_id = ? OR r.formulario_id IS NULL)
");
$stmt->bind_param("iii", $estudiante_id, $formulario_id, $formulario_id);
$stmt->execute();
$estadisticas = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Calcular porcentaje de aciertos
$porcentaje = ($estadisticas['total_preguntas'] > 0) 
    ? round(($estadisticas['respuestas_correctas'] / $estadisticas['total_preguntas']) * 100) 
    : 0;

// Asignar clase y mensaje según porcentaje
$clase_rendimiento = '';
$mensaje_rendimiento = '';

if ($porcentaje >= 80) {
    $clase_rendimiento = 'rendimiento-alto';
    $mensaje_rendimiento = 'Excelente rendimiento. El estudiante domina este tema.';
} elseif ($porcentaje >= 60) {
    $clase_rendimiento = 'rendimiento-medio-alto';
    $mensaje_rendimiento = 'Buen rendimiento. El estudiante tiene un buen conocimiento del tema.';
} elseif ($porcentaje >= 40) {
    $clase_rendimiento = 'rendimiento-medio';
    $mensaje_rendimiento = 'Rendimiento regular. El estudiante necesita reforzar algunos conceptos.';
} else {
    $clase_rendimiento = 'rendimiento-bajo';
    $mensaje_rendimiento = 'Rendimiento bajo. El estudiante necesita trabajar más en este tema.';
}

// Formatear fecha
$fecha = new DateTime($estadisticas['fecha_respuesta']);
$fecha_formateada = $fecha->format('d/m/Y H:i');

// Obtener preguntas y respuestas del estudiante
$stmt = $conex->prepare("
    SELECT 
        p.id, p.enunciado, p.opciones, p.correcta,
        r.respuesta as respuesta_usuario
    FROM preguntas p
    LEFT JOIN respuestas r ON p.id = r.pregunta_id AND r.usuario_id = ? AND r.formulario_id = ?
    WHERE p.formulario_id = ?
    ORDER BY p.id
");
$stmt->bind_param("iii", $estudiante_id, $formulario_id, $formulario_id);
$stmt->execute();
$result = $stmt->get_result();

$preguntas = [];
while ($row = $result->fetch_assoc()) {
    $row['opciones'] = json_decode($row['opciones'], true);
    $row['es_correcta'] = ($row['respuesta_usuario'] == $row['correcta']);
    $preguntas[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respuestas de <?php echo htmlspecialchars($estudiante['nombre']); ?> - Universidad CESMAG</title>
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
            --warning: #f39c12;
            --error: #c62828;
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
            background-image: url('<?php echo htmlspecialchars(!empty($formulario["imagen"]) ? $formulario["imagen"] : "img/default-bg.svg"); ?>');
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
            padding: 1.2rem 2rem;
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
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .main-container {
            max-width: 900px;
            width: 100%;
            margin: 30px auto;
            padding: 0 1rem;
            flex: 1;
        }
        
        .contenedor {
            background: var(--background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }
        
        .contenedor:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .page-title {
            margin-bottom: 2rem;
            color: var(--primary);
            font-size: 2rem;
            position: relative;
            display: inline-block;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40%;
            height: 3px;
            background-color: var(--secondary);
            border-radius: 3px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .breadcrumb a:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }
        
        .breadcrumb-separator {
            margin: 0 0.5rem;
            color: var(--text-light);
        }
        
        .student-header {
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }
        
        .student-info {
            flex: 1;
            min-width: 250px;
        }
        
        .student-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .student-name i {
            color: var(--primary);
        }
        
        .student-email {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        
        .stats-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            background: var(--neutral-light);
            padding: 1rem;
            border-radius: var(--border-radius);
            min-width: 120px;
            flex: 1;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .date-info {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .score-card {
            flex: 1;
            min-width: 200px;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        
        .rendimiento-alto {
            background-color: rgba(39, 174, 96, 0.1);
            border-left: 4px solid var(--success);
        }
        
        .rendimiento-medio-alto {
            background-color: rgba(46, 139, 87, 0.1);
            border-left: 4px solid #2E8B57;
        }
        
        .rendimiento-medio {
            background-color: rgba(243, 156, 18, 0.1);
            border-left: 4px solid var(--warning);
        }
        
        .rendimiento-bajo {
            background-color: rgba(198, 40, 40, 0.1);
            border-left: 4px solid var(--error);
        }
        
        .score-badge {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .rendimiento-alto .score-badge {
            color: var(--success);
        }
        
        .rendimiento-medio-alto .score-badge {
            color: #2E8B57;
        }
        
        .rendimiento-medio .score-badge {
            color: var(--warning);
        }
        
        .rendimiento-bajo .score-badge {
            color: var(--error);
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--neutral);
        }
        
        .message-card {
            background: #f0f7ff;
            padding: 1.2rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            border-left: 4px solid var(--primary);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .message-card i {
            font-size: 1.4rem;
            color: var(--primary);
        }
        
        .question-card {
            background: var(--neutral-light);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .question-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
        }
        
        .question-card.correct {
            border-left: 4px solid var(--success);
        }
        
        .question-card.incorrect {
            border-left: 4px solid var(--error);
        }
        
        .question-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }
        
        .question-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .question-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            flex: 1;
        }
        
        .status-icon {
            font-size: 1.5rem;
        }
        
        .status-icon.correct {
            color: var(--success);
        }
        
        .status-icon.incorrect {
            color: var(--error);
        }
        
        .options-list {
            display: grid;
            gap: 12px;
            margin: 1.2rem 0;
        }
        
        .option {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            background: var(--background);
            border: 1px solid var(--neutral);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .option:hover {
            border-color: var(--primary-light);
            box-shadow: var(--shadow-sm);
        }
        
        .option.correct-option {
            background-color: rgba(39, 174, 96, 0.1);
            border-color: var(--success);
        }
        
        .option.correct-option::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: var(--success);
        }
        
        .option.incorrect-option {
            background-color: rgba(198, 40, 40, 0.1);
            border-color: var(--error);
            text-decoration: line-through;
        }
        
        .option.incorrect-option::after {
            content: '\f00d';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 15px;
            color: var(--error);
        }
        
        .option-letter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: var(--neutral);
            border-radius: 50%;
            color: var(--text);
            margin-right: 15px;
            font-weight: 600;
        }
        
        .option.user-selected {
            border-width: 2px;
        }
        
        .result-info {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--neutral);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .result-info.correct {
            color: var(--success);
        }
        
        .result-info.incorrect {
            color: var(--error);
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
        
        .btn-danger {
            background-color: var(--secondary);
            color: white;
            border: 2px solid transparent;
        }
        
        .btn-danger:hover {
            background-color: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.2);
            transform: translateY(-2px);
        }
        
        .btn-accent {
            background-color: var(--accent);
            color: var(--text);
            border: 2px solid transparent;
        }
        
        .btn-accent:hover {
            background-color: var(--accent-light);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .btn-outline-light {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--background);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            max-width: 500px;
            width: 90%;
        }
        
        .modal-title {
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .modal-title i {
            color: var(--secondary);
        }
        
        .modal-body {
            margin-bottom: 1.5rem;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
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
        
        /* Responsividad */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }
            
            .main-container {
                padding: 0 0.8rem;
            }
            
            .contenedor {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.6rem;
            }
            
            .student-header {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .score-card {
                width: 100%;
            }
            
            .question-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .status-icon {
                position: absolute;
                top: 1rem;
                right: 1rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functionality
            const deleteButton = document.querySelector('.delete-student-btn');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            const deleteModal = document.getElementById('deleteStudentModal');
            
            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    deleteModal.style.display = 'flex';
                });
            }
            
            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteModal.style.display = 'none';
                });
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <div class="bg-container"></div>
    
    <header class="header">
        <div class="university-logo">
            <i class="fas fa-graduation-cap"></i>
            <span>Universidad CESMAG</span>
        </div>
        <div class="header-actions">
            <a href="formularios_profesor.php" class="btn btn-outline-light">
                <i class="fas fa-home"></i> Inicio
            </a>
        </div>
    </header>
    
    <div class="main-container">
        <div class="breadcrumb">
            <a href="formularios_profesor.php">Inicio</a>
            <span class="breadcrumb-separator">/</span>
            <a href="resultados_profesor.php?id=<?php echo $formulario_id; ?>">Resultados</a>
            <span class="breadcrumb-separator">/</span>
            <span>Respuestas de estudiante</span>
        </div>
        
        <div class="contenedor">
            <div class="student-header">
                <div class="student-info">
                    <div class="student-name">
                        <i class="fas fa-user-graduate"></i>
                        <span><?php echo htmlspecialchars($estudiante['nombre']); ?></span>
                    </div>

                    <div class="date-info">
                        <i class="far fa-calendar-alt"></i> Respondido el: <?php echo $fecha_formateada; ?>
                    </div>
                </div>
                
                <div class="score-card <?php echo $clase_rendimiento; ?>">
                    <div class="score-badge"><?php echo $porcentaje; ?>%</div>
                    <p><?php echo $estadisticas['respuestas_correctas']; ?> de <?php echo $estadisticas['total_preguntas']; ?> correctas</p>
                </div>
            </div>
            
            <div class="message-card">
                <i class="fas fa-info-circle"></i>
                <p><?php echo $mensaje_rendimiento; ?></p>
            </div>
            
            <h2 class="section-title">
                <i class="fas fa-clipboard-check"></i> 
                Detalle de respuestas
            </h2>
            
            <?php foreach ($preguntas as $index => $pregunta): ?>
            <div class="question-card <?php echo $pregunta['es_correcta'] ? 'correct' : 'incorrect'; ?>">
                <div class="question-header">
                    <span class="question-number"><?php echo $index + 1; ?>.</span>
                    <div class="question-text"><?php echo htmlspecialchars($pregunta['enunciado']); ?></div>
                    <div class="status-icon <?php echo $pregunta['es_correcta'] ? 'correct' : 'incorrect'; ?>">
                        <i class="fas <?php echo $pregunta['es_correcta'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    </div>
                </div>
                
                <div class="options-list">
                    <?php foreach (['a', 'b', 'c', 'd'] as $letra): ?>
                        <?php 
                            $option_class = '';
                            if ($letra == $pregunta['correcta']) {
                                $option_class = 'correct-option';
                            }
                            if ($letra == $pregunta['respuesta_usuario'] && !$pregunta['es_correcta']) {
                                $option_class = 'incorrect-option';
                            }
                            $user_selected = ($letra == $pregunta['respuesta_usuario']) ? 'user-selected' : '';
                        ?>
                        <div class="option <?php echo $option_class; ?> <?php echo $user_selected; ?>">
                            <span class="option-letter"><?php echo $letra; ?></span>
                            <?php echo htmlspecialchars($pregunta['opciones'][$letra]); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="result-info <?php echo $pregunta['es_correcta'] ? 'correct' : 'incorrect'; ?>">
                    <i class="fas <?php echo $pregunta['es_correcta'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                    <p>
                        <strong>Respuesta del estudiante: <?php echo strtoupper($pregunta['respuesta_usuario']); ?></strong>
                        <?php if (!$pregunta['es_correcta']): ?>
                            - La respuesta correcta era: <strong><?php echo strtoupper($pregunta['correcta']); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="actions">
                <div>
                    <a href="resultados_profesor.php?id=<?php echo $formulario_id; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Volver a resultados
                    </a>
                </div>
                
                <button class="btn btn-danger delete-student-btn">
                    <i class="fas fa-trash-alt"></i> Eliminar respuestas
                </button>
            </div>
        </div>
    </div>
    
    <!-- Modal para eliminar respuestas del estudiante -->
    <div id="deleteStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Eliminar respuestas</h4>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar todas las respuestas de <strong><?php echo htmlspecialchars($estudiante['nombre']); ?></strong> para este formulario?</p>
                <p><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline close-modal">Cancelar</button>
                <form method="post" action="resultados_profesor.php?id=<?php echo $formulario_id; ?>">
                    <input type="hidden" name="delete_user_responses" value="1">
                    <input type="hidden" name="user_id" value="<?php echo $estudiante_id; ?>">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Universidad CESMAG. Todos los derechos reservados.</p>
    </footer>
</body>
</html>