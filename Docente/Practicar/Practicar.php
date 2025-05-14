<?php
session_start();
include("../../base de datos/con_db.php");

// Consultar los juegos desde la base de datos
$query = "SELECT id, nombre, descripcion, imagen, url FROM juegos ORDER BY fecha_registro DESC";
$result = mysqli_query($conex, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juegos Educativos</title>
    <meta name="description" content="Explorar juegos educativos interactivos en la plataforma SaberQuest.">
    <link rel="stylesheet" href="../../assets/src_juegos/css/ver_juegos.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">

</head>
<body>
    <!-- Header -->
    <header class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="logo">
                    <a href="../index_admin.php">
                        <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                    </a>
                </div>
                <nav class="nav">
                    <a href="../index_docente.php" class="nav-link">Inicio</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">Juegos</h1>
            <div class="title-underline"></div>
            
            <div class="games-grid">

                <!-- Dynamic game cards from database -->
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<div class="game-card">';
                        echo '<div class="game-image">';
                        echo '<img src="' . htmlspecialchars($row['imagen']) . '" alt="' . htmlspecialchars($row['nombre']) . '">';
                        echo '</div>';
                        echo '<div class="game-info">';
                        echo '<h3>' . htmlspecialchars($row['nombre']) . '</h3>';
                        echo '<p>' . htmlspecialchars($row['descripcion']) . '</p>';
                        echo '<div class="card-actions">';
                        echo '<a href="' . htmlspecialchars($row['url']) . '" class="btn btn-play" target="_blank">';
                        echo '<i class="fas fa-play"></i> Jugar ahora';
                        echo '</a>';
                        echo '</form>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-games">';
                    echo '<p>No hay juegos disponibles en este momento.</p>';
                    echo '<p>¡Crea el primero haciendo clic en "Crear nuevo juego"!</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript para interactividad -->
    <script>
        // Funcionalidad para tarjetas al pasar el ratón (hover)
        document.addEventListener('DOMContentLoaded', function() {
            // Obtener todas las tarjetas de juegos
            const gameCards = document.querySelectorAll('.game-card');
            
            // Añadir efecto de elevación en hover
            gameCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                    this.style.boxShadow = '0 8px 15px rgba(0, 0, 0, 0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                });
            });
        });
    </script>
</body>
</html>
