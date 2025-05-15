<?php
session_start();
include("../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Admin</title>
    <meta name="description" content="Personal portfolio of John Doe, a web developer specializing in frontend development.">
    <link rel="stylesheet" href="../assets/src_index/css/style.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../assets/img/favicon.png" rel="icon">
    <link href="../assets/img/favicon.png" rel="apple-touch-icon">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="#home">
                        <img src="../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" style="height:50px;">
                    </a>
                </div>                
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="http://localhost/proyecto_saberquest/base%20de%20datos/cerrar.php" class="nav-link">Cerrar Sesion</a></li>
                    </ul>
                </nav>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Bienvenido Administrador</h1>
                    <p>La plataforma educativa para aprender, practicar y crecer, impulsando el progreso profesional de cada estudiante.</p>
                    <div class="hero-btns">
                        <a href="#projects" class="btn btn-primary">Gestionar</a>
                    </div>
                </div>
                <div class="hero-image">
                    <div class="hero-shape"></div>
                </div>
                <div class="scroll-down">
                    <a href="#about">
                        <span>Desliza</span>
                        <i class="fas fa-chevron-down"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about section" id="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Sobre Nosotros</h2>
                <div class="section-line"></div>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <p>En SaberQuest, entendemos que una gestión académica eficiente requiere herramientas que no solo faciliten la enseñanza, sino que impulsen el rendimiento institucional. Nacimos como una iniciativa académica con la visión de transformar la forma en que se administra y accede al conocimiento, y hoy somos una plataforma integral diseñada para fortalecer la calidad educativa desde la administración.</p>
                    <p>Nuestra solución permite a los administradores monitorear y optimizar procesos clave como la creación de simulacros, la gestión de prácticas académicas, el uso de recursos interactivos y la visualización de resultados en tiempo real. Con SaberQuest, las instituciones cuentan con un sistema que respalda la toma de decisiones basada en datos, mejora la experiencia educativa y promueve la innovación pedagógica.</p>
                    <p>Gracias al trabajo de un equipo multidisciplinario comprometido con la educación y la tecnología, continuamos evolucionando para adaptarnos a las necesidades cambiantes del entorno académico. SaberQuest es hoy un aliado estratégico para estudiantes y docentes que buscan una educación más dinámica, inclusiva y orientada a resultados.</p>
                    <div class="about-details">
                        <div class="about-detail">
                            <i class="fas fa-book-reader"></i>
                            <div>
                                <h3>Crea</h3>
                                <p>Desarrolla Simulacros</p>
                            </div>
                        </div>
                        <div class="about-detail">
                            <i class="fas fa-gamepad"></i>
                            <div>
                                <h3>Enseña</h3>
                                <p>Mediante juegos Interactivos</p>
                            </div>
                        </div>
                        <div class="about-detail">
                            <i class="fas fa-chart-bar"></i>
                            <div>
                                <h3>Evalua</h3>
                                <p> Consulta Resultados</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="../assets/src_index/img/image.png" alt="Ilustración SaberQuest">
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="projects section" id="projects">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">SABERQUEST</h2>
                <div class="section-line"></div>
            </div>
            <div class="projects-filter">
                <button class="filter-btn active" data-filter="Simulacro">Simulacros</button>
                <button class="filter-btn" data-filter="Practicar">Practicar</button>
                <button class="filter-btn" data-filter="Resultados">Resultados</button>
                <button class="filter-btn" data-filter="Usuarios">Usuarios</button>
            </div>
            <div class="projects-grid">
                <!-- Card 1 -->
                <a href="Simulacros/create_formulario.php" target="_blank" class="project-card" data-category="Simulacro">
                    <div class="project-image">
                        <img src="../assets/src_index/img/cs.png" alt="Simulacro">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Crear Simulacro</h3>
                        <p class="project-description">Accede a la herramienta para diseñar simulacros educativos adaptados a distintos temas y niveles, con opciones de personalización y gestión flexible.</p>
                    </div>
                </a>
                <!-- Card 2 -->
                <a href="Simulacros/formularios.php" target="_blank" class="project-card" data-category="Simulacro">
                    <div class="project-image">
                        <img src="../assets/src_index/img/vs.png" alt="Ver Simulacros">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Ver Simulacros</h3>
                        <p class="project-description">Consulta los simulacros disponibles en la plataforma, visualiza su contenido y realiza un seguimiento de su uso.</p>
                    </div>
                </a>
                <!-- Card 3 -->
                <a href="practicar/crear_juegos.php" target="_blank" class="project-card" data-category="Practicar">
                    <div class="project-image">
                        <img src="../assets/src_index/img/cj.png" alt="Crear Juego">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Crear Juego</h3>
                        <p class="project-description">Diseña y gestiona juegos educativos interactivos que promuevan el aprendizaje dinámico y la participación activa de los usuarios.</p>
                    </div>
                </a>
                <!-- Card 4 -->
                <a href="practicar/ver_juegos.php" target="_blank" class="project-card" data-category="Practicar">
                    <div class="project-image">
                        <img src="../assets/src_index/img/pr.png" alt="Practicar">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Practicar</h3>
                        <p class="project-description">Explora actividades y ejercicios diseñados para reforzar conocimientos mediante la práctica continua y personalizada.</p>
                    </div>
                </a>
                <!-- Card 5 -->
                <a href="Simulacros/ver_todos_formularios.php" target="_blank" class="project-card" data-category="Resultados">
                    <div class="project-image">
                        <img src="../assets/src_index/img/re.png" alt="Visualizar Resultados">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Visualizar Resultados</h3>
                        <p class="project-description">Consulta los resultados obtenidos en las diferentes actividades para evaluar el progreso y desempeño de los estudiantes.</p>
                    </div>
                </a>
                <!-- Card 6 -->
                <a href="Usuarios/usuarios.php" target="_blank" class="project-card" data-category="Usuarios">
                    <div class="project-image">
                        <img src="../assets/src_index/img/us.png" alt="Gestionar Usuarios">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Gestionar Usuarios</h3>
                        <p class="project-description">Administra la información y el acceso de los usuarios registrados en la plataforma de forma organizada y segura.</p>
                    </div>
                </a>
            </div>            
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
            <div class="footer-container">
                <p>&copy; 2025 SABERQUEST. Todos los derechos reservados.</p>
            </div>

    </footer>
    

    <!-- Back to Top Button -->
    <a href="#home" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- JavaScript -->
    <script src="../assets/src_index/js/script.js"></script>
</body>
</html>
