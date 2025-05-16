<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Estudiante') {
    header('Location: ../../index.php');
    exit;
}

// Validar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $conex->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nombre_usuario);
$stmt->fetch();
$stmt->close();

// Obtener los formularios que el usuario ha respondido (sin duplicados)
$stmt = $conex->prepare("
    SELECT DISTINCT f.id, f.titulo, f.descripcion, f.imagen, 
       (SELECT COUNT(*) FROM preguntas WHERE formulario_id = f.id) as total_preguntas,
       (SELECT COUNT(*) FROM respuestas r 
        JOIN preguntas p ON r.pregunta_id = p.id 
        WHERE r.usuario_id = ? AND r.formulario_id = f.id AND r.respuesta = p.correcta) as respuestas_correctas,
       MAX(r.fecha) as fecha_respuesta
FROM formularios f
JOIN respuestas r ON f.id = r.formulario_id
WHERE r.usuario_id = ?
GROUP BY f.id
ORDER BY fecha_respuesta DESC

");
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$formularios_respondidos = [];
while ($row = $result->fetch_assoc()) {
    // Calcular porcentaje de aciertos
    $porcentaje = ($row['total_preguntas'] > 0)
        ? round(($row['respuestas_correctas'] / $row['total_preguntas']) * 100)
        : 0;

    // Asignar clase de rendimiento según porcentaje
    $clase_rendimiento = '';
    if ($porcentaje >= 80) {
        $clase_rendimiento = 'rendimiento-alto';
    } elseif ($porcentaje >= 50) {
        $clase_rendimiento = 'rendimiento-medio';
    } else {
        $clase_rendimiento = 'rendimiento-bajo';
    }

    // Formatear fecha
    $fecha = new DateTime($row['fecha_respuesta']);
    $fecha_formateada = $fecha->format('d/m/Y H:i');

    $row['porcentaje'] = $porcentaje;
    $row['clase_rendimiento'] = $clase_rendimiento;
    $row['fecha_formateada'] = $fecha_formateada;

    $formularios_respondidos[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Resultados - SABERQUEST</title>
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

        .header {
            background-color: var(--primary);
            color: white;
            padding: 1.2rem 11rem;
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

        .welcome-card {
            background: var(--background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .welcome-card h2 {
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .result-card {
            background: var(--background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-image {
            height: 160px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-content {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.8rem;
        }

        .card-description {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .card-stats {
            background: var(--neutral-light);
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--neutral);
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text);
            font-weight: 500;
        }

        .score-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .rendimiento-alto {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .rendimiento-medio {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .rendimiento-bajo {
            background-color: rgba(198, 40, 40, 0.1);
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

        .btn-accent {
            background-color: var(--accent);
            color: var(--primary);
            border: 2px solid transparent;
        }

        .btn-accent:hover {
            background-color: var(--accent-light);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
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

        .date-info {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .results-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }

            .page-title {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0 0.8rem;
            }

            .results-grid {
                grid-template-columns: 1fr;
            }

            .card-content {
                padding: 1.2rem;
            }
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
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-space">
            <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
        </div>

        <div class="nav-controls">
            <nav class="nav">
                <div class="nav-list">
                    <a class="nav-link" href="../index_estudiante.php#projects">Inicio</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <h1 class="page-title">Mis Resultados</h1>

        <?php if (empty($formularios_respondidos)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No has respondido ningún simulacro todavía</h3>
                <p>Los resultados de los simulacro que completes aparecerán aquí.</p>
                <a href="formularios_estudiante.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Explorar simulacros
                </a>
            </div>
        <?php else: ?>
            <div class="results-grid">
                <?php foreach ($formularios_respondidos as $form): ?>
                    <?php
                    // Array of default images
                    $default_images = [
                        '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada1.png',
                        '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada2.png',
                        '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada3.png',
                        '../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada4.png'
                    ];

                    // Select a random image if no image is provided in the database
                    $card_image = !empty($form['imagen']) ? $form['imagen'] : $default_images[array_rand($default_images)];
                    ?>
                    <div class="result-card">
                        <div class="card-image"
                            style="background-image: url('<?php echo htmlspecialchars($card_image); ?>')">
                            <div class="card-overlay">
                                <span class="score-badge <?php echo $form['clase_rendimiento']; ?>">
                                    <?php echo $form['respuestas_correctas']; ?> / <?php echo $form['total_preguntas']; ?>
                                    correctas
                                </span>
                            </div>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($form['titulo']); ?></h3>
                            <p class="card-description">
                                <?php echo htmlspecialchars(mb_strimwidth($form['descripcion'], 0, 120, '...')); ?></p>
                            <div class="date-info">
                                <i class="far fa-calendar-alt"></i> <?php echo $form['fecha_formateada']; ?>
                            </div>
                        </div>
                        <div class="card-stats">
                            <div class="stat-item">
                                <span class="stat-label">Porcentaje de aciertos:</span>
                                <span class="score-badge <?php echo $form['clase_rendimiento']; ?>">
                                    <?php echo $form['porcentaje']; ?>%
                                </span>
                            </div>
                        </div>
                        <div class="card-actions">
                            <a href="ver_detalle_respuestas.php?id=<?php echo $form['id']; ?>" class="btn btn-outline">
                                <i class="fas fa-eye"></i> Ver detalle
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST. Todos los derechos reservados.</p>
    </footer>
</body>

</html>