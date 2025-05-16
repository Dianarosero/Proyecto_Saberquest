<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Docente') {
    header('Location: ../../index.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del profesor
$stmt = $conex->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nombre_profesor);
$stmt->fetch();
$stmt->close();

// Obtener todos los formularios disponibles (no solo los creados por el profesor)
$stmt = $conex->prepare("
    SELECT 
        f.id, f.titulo, f.descripcion, f.imagen,
        (SELECT COUNT(*) FROM preguntas WHERE formulario_id = f.id) as total_preguntas,
        (SELECT COUNT(DISTINCT usuario_id) FROM respuestas WHERE formulario_id = f.id) as total_respondidos
    FROM formularios f
    ORDER BY f.fecha_creacion DESC
");
$stmt->execute();
$result = $stmt->get_result();


$formularios = [];
while ($row = $result->fetch_assoc()) {
    // Determinar si el formulario tiene respuestas
    $row['tiene_respuestas'] = ($row['total_respondidos'] > 0);

    $formularios[] = $row;
}
$stmt->close();


// Agrupar formularios por creador para una mejor organización
$formularios_agrupados = [
    'todos' => [
        'nombre' => 'Todos los formularios',
        'formularios' => $formularios
    ]
];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados Simulacros - SABERQUEST</title>
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
            --info: #3498db;
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

        .creador-section {
            margin: 3rem 0;
        }

        .formularios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .form-card {
            background: var(--background);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .form-card:hover {
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

        .card-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            color: white;
        }

        .badge-success {
            background-color: var(--success);
        }

        .badge-info {
            background-color: var(--info);
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

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            min-width: 30px;
        }

        .badge-success {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success);
        }

        .badge-info {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info);
        }

        .badge-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .badge-error {
            background-color: rgba(198, 40, 40, 0.1);
            color: var(--error);
        }

        .card-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            gap: 0.5rem;
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

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
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

        .btn-secondary {
            background-color: var(--info);
            color: white;
            border: 2px solid transparent;
        }

        .btn-secondary:hover {
            background-color: #2980b9;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
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

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
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

        /* Status inactivo */
        .disabled-card {
            opacity: 0.7;
            position: relative;
        }

        .disabled-card::before {
            content: 'Sin respuestas disponibles';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.8rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            z-index: 10;
            white-space: nowrap;
        }

        .disabled-card .card-content,
        .disabled-card .card-stats,
        .disabled-card .card-actions {
            filter: blur(2px);
            pointer-events: none;
        }

        /* Responsividad */
        @media (max-width: 768px) {
            .header {
                padding: 1rem;
            }

            .main-container {
                padding: 0 0.8rem;
            }

            .formularios-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }

            .page-title {
                font-size: 1.6rem;
            }


        }

        @media (max-width: 480px) {
            .formularios-grid {
                grid-template-columns: 1fr;
            }

            .card-content {
                padding: 1.2rem;
            }

            .card-stats {
                padding: 1rem;
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
        });
    </script>
</head>

<body>
    <header class="header">
        <div class="logo-space">
            <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
        </div>
        <div class="nav-controls">
            <nav class="nav">
                <div class="nav-list">
                    <a class="nav-link" href="../index_docente.php#projects">Inicio</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="main-container">
        <h1 class="page-title">Simulacros Disponibles</h1>

        <?php if (empty($formularios)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No hay simulacros disponibles</h3>
                <p>Aún no se han creado simulacros en el sistema.</p>
            </div>
        <?php else: ?>

            <!-- Tab: Todos los formularios -->
            <div id="tab-todos" class="tab-content active">
                <?php foreach ($formularios_agrupados as $creador_id => $grupo): ?>
                    <div class="creador-section">
                        <div class="formularios-grid">
                            <?php foreach ($grupo['formularios'] as $form): ?>
                                <div class="form-card <?php echo !$form['tiene_respuestas'] ? 'disabled-card' : ''; ?>">
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

                                    <div class="card-image" style="background-image: url('<?php echo htmlspecialchars($card_image); ?>')">
                                        <div class="card-overlay"></div>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title"><?php echo htmlspecialchars($form['titulo']); ?></h3>
                                        <p class="card-description"><?php echo htmlspecialchars(mb_strimwidth($form['descripcion'], 0, 120, '...')); ?></p>
                                    </div>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-label">Preguntas:</span>
                                            <span class="badge badge-info"><?php echo $form['total_preguntas']; ?></span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-label">Respondido por:</span>
                                            <span class="badge badge-<?php echo $form['total_respondidos'] > 0 ? 'success' : 'warning'; ?>">
                                                <?php echo $form['total_respondidos']; ?> estudiantes
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-actions">
                                        <a href="resultados_profesor.php?id=<?php echo $form['id']; ?>" class="btn btn-primary btn-sm <?php echo !$form['tiene_respuestas'] ? 'btn-disabled' : ''; ?>">
                                            <i class="fas fa-chart-bar"></i> Ver resultados
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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