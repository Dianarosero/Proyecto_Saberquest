<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || empty($_SESSION['usuario_id'])) {
    // Redirigir a la página de login si no está autenticado
    header('Location: ../index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
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
                    <h1>Bienvenido Estudiante</span></h1>
                    <p>La plataforma educativa para aprender, practicar y crecer, impulsando el progreso profesional de cada estudiante.</p>
                    <div class="hero-btns">
                        <a href="#projects" class="btn btn-primary">Comenzar</a>
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
                    <p>SaberQuest nació como una iniciativa académica orientada a transformar la forma en que estudiantes, docentes y administradores interactúan con el conocimiento. Desde sus inicios, la plataforma fue diseñada con el propósito de ofrecer herramientas digitales innovadoras para la enseñanza, la evaluación y el aprendizaje autónomo.</p>
                    <p>Con el paso del tiempo, SaberQuest ha evolucionado de un proyecto universitario a una solución educativa integral que apoya la gestión de simulacros, prácticas, juegos interactivos y visualización de resultados. Gracias al compromiso de un equipo multidisciplinario apasionado por la educación y la tecnología, la plataforma continúa creciendo y adaptándose a las necesidades del entorno académico moderno.</p>
                    <p>Hoy, SaberQuest se consolida como un espacio virtual donde el aprendizaje es dinámico, inclusivo y accesible para todos, fortaleciendo las habilidades de los estudiantes y facilitando el trabajo pedagógico de los docentes.</p>
                    <div class="about-details">
                        <div class="about-detail">
                            <i class="fas fa-book-reader"></i>
                            <div>
                                <h3>Practica</h3>
                                <p>Mediante juegos</p>
                            </div>
                        </div>
                        <div class="about-detail">
                            <i class="fas fa-gamepad"></i>
                            <div>
                                <h3>Desarrolla</h3>
                                <p>Simulacros</p>
                            </div>
                        </div>
                        <div class="about-detail">
                            <i class="fas fa-chart-bar"></i>
                            <div>
                                <h3>Evaluate</h3>
                                <p> Consulta tus Resultados</p>
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
            <div class="projects-grid">
                <!-- Card 1 -->
                <a href="Practicar/practicar.php" target="_blank" class="project-card" data-category="Practicar">
                    <div class="project-image">
                        <img src="../assets/src_index/img/pr.png" alt="Practicar">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Practicar</h3>
                        <p class="project-description">Explora actividades y ejercicios diseñados para reforzar conocimientos mediante la práctica continua y personalizada.</p>
                    </div>
                </a>
                <!-- Card 2 -->
                 <a href="Simulacros/formularios_estudiante.php" target="_blank" class="project-card" data-category="Simulacro">
                    <div class="project-image">
                        <img src="../assets/src_index/img/vs.png" alt="Ver Simulacros">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Realiza Simulacros</h3>
                        <p class="project-description">Consulta los simulacros disponibles en la plataforma, visualiza su contenido y desarrolla.</p>
                    </div>
                </a>
                <!-- Card 3 -->
                <a href="Resultados/resultados.php" target="_blank" class="project-card" data-category="Resultados">
                    <div class="project-image">
                        <img src="../assets/src_index/img/re.png" alt="Visualizar Resultados">
                    </div>
                    <div class="project-info">
                        <h3 class="project-title">Consulta tus Resultados</h3>
                        <p class="project-description">Consulta los resultados obtenidos en las diferentes actividades para evaluar el progreso y desempeño como estudiante.</p>
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

    <a href="#home" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

  
    <!-- JavaScript -->
    <script src="../assets/src_index/js/script.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        var nombreCompleto = localStorage.getItem('nombreCompleto') || '';
        var primerNombre = nombreCompleto.trim().split(' ')[0];
      if(primerNombre) {
          document.getElementById('nombre-usuario').textContent = primerNombre;
      } else {
          document.getElementById('nombre-usuario').textContent = 'Estudiante';
      }  
    });
</script>
</body>
</html>
