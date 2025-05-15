<?php
session_start();
include("../../base de datos/con_db.php");

// Consulta para obtener todos los
$sql = "SELECT id, titulo, descripcion, imagen FROM formularios ORDER BY id DESC";
$result = $conex->query($sql);

// Función para obtener una imagen predeterminada basada en el ID (para simulación)
function obtenerImagenPredeterminada($id)
{
    $imagenes = [
        "../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada1.png",
        "../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada2.png",
        "../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada3.png",
        "../../assets/src_simulacros/img_simulacros/predeterminadas/predeterminada4.png"
    ];
    $index = $id % count($imagenes);
    return $imagenes[$index];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Simulacros</title>

    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <!-- Google Fonts - Montserrat & Lato -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        // Script para garantizar el footer al fondo
        document.addEventListener('DOMContentLoaded', function() {
            function adjustFooter() {
                const bodyHeight = document.body.offsetHeight;
                const windowHeight = window.innerHeight;
                const footer = document.querySelector('.main-footer');

                if (bodyHeight < windowHeight) {
                    footer.style.position = 'fixed';
                    footer.style.bottom = '0';
                } else {
                    footer.style.position = 'relative';
                    footer.style.bottom = 'auto';
                }
            }

            // Ejecutar al cargar y al cambiar el tamaño de la ventana
            adjustFooter();
            window.addEventListener('resize', adjustFooter);
        });
    </script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            font-family: 'Lato', sans-serif;
            background-color: #f5f5f5;
            color: #333333;
            line-height: 1.6;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        body {
            position: relative;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: #003366;
        }

        a {
            text-decoration: none;
            color: #003366;
        }

        /* Header Styles */
        .top-header {
            background-color: #003366;
            color: #FFFFFF;
            padding: 1.2rem 12rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-space {
            display: flex;
            align-items: center;
        }

        .logo-placeholder {
            color: #FFFFFF;
            font-weight: bold;
            font-size: 1.2rem;
            font-family: 'Montserrat', sans-serif;
            border: 2px dashed #FFFFFF;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }

        .nav-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }


        .page-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: #004488;
            /* Azul oscuro similar al de la imagen */
            font-family: 'Poppins', sans-serif;
            margin-top: 40px;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .title-underline {
            width: 60px;
            height: 5px;
            background-color: #a51c1c;
            /* Rojo similar al de la línea */
            margin: 16px auto 0 auto;
            border-radius: 2px;
        }




        /* Main Content Container */
        .main-content {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            flex: 1;
        }

        /* Forms Grid Container */
        .forms-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            /* Siempre 3 columnas */
            gap: 1.5rem;
        }


        /* Card Styles */
        .card {
            background-color: #FFFFFF;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        /* Create Form Card */
        .create-form-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            height: 100%;
            border: 2px dashed #E0E0E0;
            min-height: 250px;
        }

        .create-form-card:hover {
            border-color: #003366;
        }

        .create-icon {
            font-size: 3.5rem;
            color: #003366;
            margin-bottom: 1rem;
            transition: color 0.3s;
        }

        .create-form-card:hover .create-icon {
            color: #004488;
        }

        .create-form-card h2 {
            margin-bottom: 0.5rem;
        }

        .create-form-card p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Form Card */
        .form-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card-image {
            height: 150px;
            overflow: hidden;
            position: relative;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .form-card:hover .card-image img {
            transform: scale(1.05);
        }

        .card-content {
            padding: 1.2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-content h3 {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .form-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
        }

        /* Button Styles */
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #003366;
            color: #FFFFFF;
            flex: 1;
        }

        .edit-btn:hover {
            background-color: #004488;
            transform: translateY(-2px);
        }

        .delete-btn {
            background-color: #B22222;
            color: #FFFFFF;
            flex: 1;
        }

        .delete-btn:hover {
            background-color: #D22F2F;
            transform: translateY(-2px);
        }

        .btn i {
            font-size: 0.9rem;
        }

        /* Footer Styles */
        .main-footer {
            text-align: center;
            padding: 1.5rem;
            margin-top: 2rem;
            background-color: #003366;
            color: #FFFFFF;
            font-size: 0.9rem;
            width: 100%;
        }

        /* Responsive Styles */
        @media screen and (max-width: 992px) {
            .forms-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }

            .header-content {
                padding: 0 1rem;
            }

            .nav-controls {
                gap: 0.5rem;
            }
        }

        @media screen and (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .logo-space {
                justify-content: center;
                margin-bottom: 0.5rem;
            }

            .nav-controls {
                flex-direction: column;
                gap: 0.8rem;
            }

            .page-title h1 {
                font-size: 1.5rem;
                text-align: center;
            }

            .main-content {
                padding: 0 1rem;
            }

            .forms-container {
                grid-template-columns: 1fr;
                gap: 1.2rem;
            }
        }

        @media screen and (max-width: 480px) {
            .card-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .top-header {
                padding: 1rem;
            }

            .create-form-card {
                padding: 1.5rem;
            }

            .create-icon {
                font-size: 3rem;
            }

            .card-content h3 {
                font-size: 1rem;
            }
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
    <header class="top-header">
        <div class="header-content">
            <div class="logo-space">
                <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">
            </div>
            <div class="nav-controls">
                <nav class="nav">
                    <div class="nav-list">
                        <a class="nav-link" href="../index_admin.php">Inicio</a>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="page-title">
            Simulacros
            <div class="title-underline"></div>
        </div>
        <section class="forms-container">
            <!-- Create New Form Card -->
            <div class="card create-form-card" onclick="window.location.href='create_formulario.php'">
                <div class="card-content">
                    <div class="create-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h2>Crear un Simulacro</h2>
                    <p>Diseña un nuevo simulacro personalizado</p>
                </div>
            </div>

            <!-- Existing Forms Grid -->
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()):
                    // Usar imagen del formulario si existe, si no, imagen predeterminada
                    if (!empty($row['imagen'])) {
                        $imagen = $row['imagen'];
                    } else {
                        $imagen = obtenerImagenPredeterminada($row['id']);
                    }

                    // Obtener un resumen corto de la descripción
                    $descripcion_corta = substr(htmlspecialchars($row['descripcion']), 0, 100);
                    if (strlen($row['descripcion']) > 100) {
                        $descripcion_corta .= '...';
                    }
                ?>
                    <div class="card form-card">
                        <div class="card-image">
                            <img src="<?php echo htmlspecialchars($imagen); ?>"
                                alt="<?php echo htmlspecialchars($row['titulo']); ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['titulo']); ?></h3>
                            <p class="form-description"><?php echo nl2br($descripcion_corta); ?></p>
                            <div class="card-actions">
                                <a href="ver_formulario.php?id=<?php echo $row['id']; ?>" class="btn edit-btn">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <a href="eliminar_formulario.php?id=<?php echo $row['id']; ?>" class="btn delete-btn"
                                    onclick="return confirm('¿Estás seguro de que deseas eliminar este simulacro?');">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="no-forms-message"
                    style="grid-column: span 3; text-align: center; padding: 2rem; background: #fff; border-radius: 8px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #003366; margin-bottom: 1rem;"></i>
                    <h3>No hay Simulacros disponibles</h3>
                    <p>Crea tu primer simulacro haciendo clic en "Crear un Simulacro"</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> SABERQUEST. Todos los derechos reservados.</p>
    </footer>
</body>

</html>