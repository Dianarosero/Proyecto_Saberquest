<?php
session_start();
include("../../base de datos/con_db.php");

// Consultar los juegos desde la base de datos
$query = "SELECT nombre, descripcion, imagen, url FROM juegos ORDER BY fecha_registro DESC";
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="http://localhost/proyecto_saberquest/Admin/index_admin.php" class="nav-link">Inicio</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="page-title">JUEGOS</h1>
            
            <div class="games-grid">
                <!-- Card for creating a new game -->
                <div class="create-card">
                    <div class="create-card-content">
                        <h2>Crear nuevo juego</h2>
                        <p>Añade un nuevo juego educativo a la plataforma</p>
                        <a href="crear_juegos.php" class="btn-create">Crear juego</a>
                    </div>
                </div>

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
                        echo '<a href="' . htmlspecialchars($row['url']) . '" class="btn-play">Jugar ahora</a>';
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
</body>
</html>
