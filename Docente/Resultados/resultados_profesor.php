<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Docente') {
    header('Location: ../../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$formulario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validar que formulario_id sea un número válido
if ($formulario_id <= 0) {
    $_SESSION['error'] = "ID de formulario no válido.";
    header('Location: ver_todos_formularios.php');
    exit;
}

// Verificar que el formulario existe
$stmt = $conex->prepare("
    SELECT id, titulo, descripcion, imagen, mostrar_respuestas
    FROM formularios
    WHERE id = ?
");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Formulario no encontrado.";
    header('Location: ver_todos_formularios.php');
    exit;
}

$formulario = $result->fetch_assoc();
$stmt->close();

// Procesar acción de reiniciar formulario
if (isset($_POST['reset_form']) && $_POST['reset_form'] == 1) {
    $stmt = $conex->prepare("DELETE FROM respuestas WHERE formulario_id = ?");
    $stmt->bind_param("i", $formulario_id);
    $stmt->execute();
    $stmt->close();

    // Redirigir para evitar reenvío del formulario
    header("Location: resultados_profesor.php?id=$formulario_id&reset=success");
    exit;
}

// Procesar acción de eliminar respuestas de un usuario
if (isset($_POST['delete_user_responses']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conex->prepare("DELETE FROM respuestas WHERE formulario_id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $formulario_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirigir para evitar reenvío del formulario
    header("Location: resultados_profesor.php?id=$formulario_id&delete=success");
    exit;
}

// Obtener estadísticas generales
$stmt = $conex->prepare("
    SELECT 
        COUNT(DISTINCT r.usuario_id) as total_estudiantes,
        COUNT(DISTINCT p.id) as total_preguntas,
        AVG(CASE WHEN r.respuesta = p.correcta THEN 1 ELSE 0 END) * 100 as promedio_aciertos
    FROM preguntas p
    LEFT JOIN respuestas r ON p.id = r.pregunta_id
    WHERE p.formulario_id = ? AND r.formulario_id = ?
");
$stmt->bind_param("ii", $formulario_id, $formulario_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Si no hay respuestas, establecer valores predeterminados
if (empty($stats['total_estudiantes'])) {
    $stats['total_estudiantes'] = 0;
    $stats['promedio_aciertos'] = 0;
}

// Calcular distribución de calificaciones
$stmt = $conex->prepare("
    SELECT 
        u.id as usuario_id,
        u.nombre,
        COUNT(DISTINCT p.id) as total_preguntas,
        SUM(CASE WHEN r.respuesta = p.correcta THEN 1 ELSE 0 END) as respuestas_correctas,
        MAX(r.fecha) as fecha_respuesta
    FROM usuarios u
    JOIN respuestas r ON u.id = r.usuario_id
    JOIN preguntas p ON r.pregunta_id = p.id
    WHERE r.formulario_id = ?
    GROUP BY u.id
    ORDER BY respuestas_correctas DESC, fecha_respuesta ASC
");
$stmt->bind_param("i", $formulario_id);

$stmt->execute();
$result = $stmt->get_result();

$estudiantes = [];
$rangos_calificacion = [
    'excelente' => 0,
    'bueno' => 0,
    'regular' => 0,
    'deficiente' => 0
];

while ($row = $result->fetch_assoc()) {
    // Calcular porcentaje de aciertos
    $porcentaje = ($row['total_preguntas'] > 0)
        ? round(($row['respuestas_correctas'] / $row['total_preguntas']) * 100)
        : 0;

    // Asignar clase de rendimiento según porcentaje
    $clase_rendimiento = '';
    if ($porcentaje >= 80) {
        $clase_rendimiento = 'rendimiento-alto';
        $rangos_calificacion['excelente']++;
    } elseif ($porcentaje >= 60) {
        $clase_rendimiento = 'rendimiento-medio-alto';
        $rangos_calificacion['bueno']++;
    } elseif ($porcentaje >= 40) {
        $clase_rendimiento = 'rendimiento-medio';
        $rangos_calificacion['regular']++;
    } else {
        $clase_rendimiento = 'rendimiento-bajo';
        $rangos_calificacion['deficiente']++;
    }

    // Formatear fecha
    $fecha = new DateTime($row['fecha_respuesta']);
    $fecha_formateada = $fecha->format('d/m/Y H:i');

    $row['porcentaje'] = $porcentaje;
    $row['clase_rendimiento'] = $clase_rendimiento;
    $row['fecha_formateada'] = $fecha_formateada;

    $estudiantes[] = $row;
}
$stmt->close();

// Análisis de preguntas (cuáles son las más difíciles)
$stmt = $conex->prepare("
    SELECT 
        p.id, 
        p.enunciado, 
        COUNT(r.id) as total_respuestas,
        SUM(CASE WHEN r.respuesta = p.correcta THEN 1 ELSE 0 END) as respuestas_correctas
    FROM preguntas p
    LEFT JOIN respuestas r ON p.id = r.pregunta_id
    WHERE p.formulario_id = ? AND (r.formulario_id = ? OR r.formulario_id IS NULL)
    GROUP BY p.id
    ORDER BY 
        SUM(CASE WHEN r.respuesta = p.correcta THEN 1 ELSE 0 END) / NULLIF(COUNT(r.id), 0) ASC
");
$stmt->bind_param("ii", $formulario_id, $formulario_id);
$stmt->execute();
$result = $stmt->get_result();


$preguntas_analisis = [];
while ($row = $result->fetch_assoc()) {
    $row['porcentaje_aciertos'] = ($row['total_respuestas'] > 0)
        ? round(($row['respuestas_correctas'] / $row['total_respuestas']) * 100)
        : 0;

    // Determinar la dificultad
    if ($row['porcentaje_aciertos'] <= 30) {
        $row['dificultad'] = 'Difícil';
        $row['clase_dificultad'] = 'dificil';
    } elseif ($row['porcentaje_aciertos'] <= 70) {
        $row['dificultad'] = 'Media';
        $row['clase_dificultad'] = 'media';
    } else {
        $row['dificultad'] = 'Fácil';
        $row['clase_dificultad'] = 'facil';
    }

    $preguntas_analisis[] = $row;
}
$stmt->close();

// Determinar clases para las estadísticas
$promedio_class = '';
if ($stats['promedio_aciertos'] >= 80) {
    $promedio_class = 'rendimiento-alto';
} elseif ($stats['promedio_aciertos'] >= 60) {
    $promedio_class = 'rendimiento-medio-alto';
} elseif ($stats['promedio_aciertos'] >= 40) {
    $promedio_class = 'rendimiento-medio';
} else {
    $promedio_class = 'rendimiento-bajo';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados del Formulario - Universidad CESMAG</title>
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
            max-width: 1200px;
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

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .alert-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .top-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--neutral-light);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
            color: var(--primary);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .rendimiento-alto .stat-value {
            color: var(--success);
        }

        .rendimiento-medio-alto .stat-value {
            color: #2E8B57;
            /* Sea Green */
        }

        .rendimiento-medio .stat-value {
            color: var(--warning);
        }

        .rendimiento-bajo .stat-value {
            color: var(--error);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }



        .table-responsive {
            overflow-x: auto;
            margin-bottom: 2rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .table th,
        .table td {
            padding: 0.8rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--neutral);
        }

        .table th {
            background-color: var(--primary);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table th:first-child {
            border-top-left-radius: 6px;
        }

        .table th:last-child {
            border-top-right-radius: 6px;
        }

        .table tr:nth-child(even) {
            background-color: var(--neutral-light);
        }

        .table tr:hover {
            background-color: rgba(0, 51, 102, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-align: center;
            min-width: 80px;
        }

        .badge-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .badge-error {
            background-color: rgba(198, 40, 40, 0.1);
            color: var(--error);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--primary);
            margin: 2rem 0 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--neutral);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1rem;
            border-radius: var(--border-radius);
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
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
        }

        .btn-danger:hover {
            background-color: var(--secondary-light);
            box-shadow: 0 0 0 3px rgba(178, 34, 34, 0.2);
            transform: translateY(-2px);
        }

        .btn-accent {
            background-color: var(--accent);
            color: var(--text);
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

        .tab-navigation {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--neutral);
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .tab-navigation::-webkit-scrollbar {
            height: 6px;
        }

        .tab-navigation::-webkit-scrollbar-thumb {
            background-color: var(--neutral);
            border-radius: 3px;
        }

        .tab-navigation::-webkit-scrollbar-track {
            background-color: var(--neutral-light);
        }

        .tab-item {
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            border-bottom: 3px solid transparent;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }

        .tab-item:hover {
            color: var(--primary);
        }

        .tab-item.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--neutral-light);
            border-radius: var(--border-radius);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--neutral);
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
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

            .top-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .tab-item {
                padding: 0.8rem 1.2rem;
            }
        }

        
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tabs functionality
            const tabItems = document.querySelectorAll('.tab-item');
            const tabContents = document.querySelectorAll('.tab-content');

            tabItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabItems.forEach(tab => tab.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));

                    // Add active class to clicked tab
                    this.classList.add('active');
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            // Modal functionality
            const deleteButtons = document.querySelectorAll('.delete-student-btn');
            const resetButton = document.querySelector('.reset-form-btn');
            const closeModalButtons = document.querySelectorAll('.close-modal');
            const deleteModal = document.getElementById('deleteStudentModal');
            const resetModal = document.getElementById('resetFormModal');
            const deleteForm = document.getElementById('deleteUserForm');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');

                    document.getElementById('deleteUserName').textContent = userName;
                    document.getElementById('deleteUserId').value = userId;
                    deleteModal.style.display = 'flex';
                });
            });

            if (resetButton) {
                resetButton.addEventListener('click', function() {
                    resetModal.style.display = 'flex';
                });
            }

            closeModalButtons.forEach(button => {
                button.addEventListener('click', function() {
                    deleteModal.style.display = 'none';
                    resetModal.style.display = 'none';
                });
            });

            // Close modals when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
                if (event.target === resetModal) {
                    resetModal.style.display = 'none';
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
            <a href="../index_docente.php" class="btn btn-outline-light">
                <i class="fas fa-home"></i> Inicio
            </a>
        </div>
    </header>

    <div class="main-container">
        <div class="breadcrumb">
            <a href="ver_todos_formularios.php">Simulacros</a>
            <span class="breadcrumb-separator">/</span>
            <span>Resultados</span>
        </div>

        <?php if (isset($_GET['reset']) && $_GET['reset'] == 'success'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <p>Todas las respuestas del formulario han sido eliminadas correctamente.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['delete']) && $_GET['delete'] == 'success'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <p>Las respuestas del estudiante han sido eliminadas correctamente.</p>
            </div>
        <?php endif; ?>

        <div class="contenedor">
            <div class="top-actions">
                <h1 class="page-title">Resultados: <?php echo htmlspecialchars($formulario['titulo']); ?></h1>

                <div>
                    <button class="btn btn-danger reset-form-btn" <?php echo empty($estudiantes) ? 'disabled' : ''; ?>>
                        <i class="fas fa-trash-alt"></i> Reiniciar formulario
                    </button>
                </div>
            </div>

            <?php if (empty($estudiantes)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No hay respuestas todavía</h3>
                    <p>Ningún estudiante ha respondido este formulario aún.</p>
                </div>
            <?php else: ?>

                <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-value"><?php echo $stats['total_estudiantes']; ?></div>
                        <div class="stat-label">Estudiantes</div>
                    </div>

                    <div class="stat-card <?php echo $promedio_class; ?>">
                        <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                        <div class="stat-value"><?php echo round($stats['promedio_aciertos'], 1); ?>%</div>
                        <div class="stat-label">Promedio de aciertos</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                        <div class="stat-value"><?php echo $stats['total_preguntas']; ?></div>
                        <div class="stat-label">Preguntas</div>
                    </div>
                </div>

                <div class="tab-navigation">
                    <div class="tab-item active" data-tab="tab-estudiantes">
                        <i class="fas fa-users"></i> Estudiantes
                    </div>
                    <div class="tab-item" data-tab="tab-preguntas">
                        <i class="fas fa-question-circle"></i> Análisis de preguntas
                    </div>
                </div>

                <div id="tab-estudiantes" class="tab-content active">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Calificación</th>
                                    <th>Porcentaje</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $index => $estudiante): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($estudiante['nombre']); ?></td>
                                        <td><?php echo $estudiante['respuestas_correctas']; ?>/<?php echo $estudiante['total_preguntas']; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            if ($estudiante['porcentaje'] >= 80) {
                                                $badge_class = 'badge-success';
                                            } elseif ($estudiante['porcentaje'] >= 60) {
                                                $badge_class = 'badge-success';
                                            } elseif ($estudiante['porcentaje'] >= 40) {
                                                $badge_class = 'badge-warning';
                                            } else {
                                                $badge_class = 'badge-error';
                                            }
                                            ?>
                                            <span
                                                class="badge <?php echo $badge_class; ?>"><?php echo $estudiante['porcentaje']; ?>%</span>
                                        </td>
                                        <td><?php echo $estudiante['fecha_formateada']; ?></td>
                                        <td>
                                            <div class="table-actions">
                                                <a href="ver_respuestas_estudiante.php?form_id=<?php echo $formulario_id; ?>&user_id=<?php echo $estudiante['usuario_id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                                <button class="btn btn-danger btn-sm delete-student-btn"
                                                    data-user-id="<?php echo $estudiante['usuario_id']; ?>"
                                                    data-user-name="<?php echo htmlspecialchars($estudiante['nombre']); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="tab-preguntas" class="tab-content">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Enunciado</th>
                                    <th>Dificultad</th>
                                    <th>Aciertos</th>
                                    <th>Porcentaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($preguntas_analisis as $index => $pregunta): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($pregunta['enunciado']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            if ($pregunta['clase_dificultad'] == 'dificil') {
                                                $badge_class = 'badge-error';
                                            } elseif ($pregunta['clase_dificultad'] == 'media') {
                                                $badge_class = 'badge-warning';
                                            } else {
                                                $badge_class = 'badge-success';
                                            }
                                            ?>
                                            <span
                                                class="badge <?php echo $badge_class; ?>"><?php echo $pregunta['dificultad']; ?></span>
                                        </td>
                                        <td><?php echo $pregunta['respuestas_correctas']; ?>/<?php echo $pregunta['total_respuestas']; ?>
                                        </td>
                                        <td>
                                            <span
                                                class="badge <?php echo $badge_class; ?>"><?php echo $pregunta['porcentaje_aciertos']; ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para eliminar respuestas de un estudiante -->
    <div id="deleteStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Eliminar respuestas</h4>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar todas las respuestas de <strong id="deleteUserName"></strong>
                    para este formulario?</p>
                <p><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline close-modal">Cancelar</button>
                <form id="deleteUserForm" method="post">
                    <input type="hidden" name="delete_user_responses" value="1">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para reiniciar formulario -->
    <div id="resetFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Reiniciar formulario</h4>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar <strong>todas las respuestas</strong> de todos los estudiantes
                    para este formulario?</p>
                <p><small>Esta acción no se puede deshacer y afectará a <?php echo $stats['total_estudiantes']; ?>
                        estudiantes.</small></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline close-modal">Cancelar</button>
                <form method="post">
                    <input type="hidden" name="reset_form" value="1">
                    <button type="submit" class="btn btn-danger">Reiniciar formulario</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Universidad CESMAG. Todos los derechos reservados.</p>
    </footer>
</body>

</html>