<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Juego</title>
    <meta name="description" content="Crea juegos educativos para la plataforma SaberQuest.">
    <link rel="stylesheet" href="../../assets/src_juegos/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index_admin.php">
                        <img src="../../assets/img/Logo_fondoazul.png" alt="Logo SaberQuest" class="logo-img">
                    </a>
                </div>                
                <nav class="nav">
                    <ul class="nav-list">
                        <li><a href="../index_admin.php#projects" class="nav-link">Inicio</a></li>
                    </ul>
                </nav>
                <!-- Mobile menu toggle -->

            </div>
        </div>
    </header>

    <!-- Main Content Section -->
    <section class="main-content" id="home">
        <div class="container">
            <div class="content-wrapper">
                <!-- Left Column - Information -->
                <div class="info-column">
                    <div class="info-content">
                    <div class="section-header">
                    <h2 class="section-title">Crear Juegos</h2>
                    <div class="section-line"></div>
                </div>
                        <p></p>
                        <p>Crear tu propio juego es más fácil de lo que parece. Primero, dirígete a Genially y diseña tu juego interactivo desde cero o utilizando una de sus plantillas. Cuando hayas terminado, la plataforma te dará un enlace (link) para compartir tu creación. Una vez tengas ese link, vuelve a esta página y completa el formulario: ponle un nombre atractivo a tu juego, escribe una breve descripción que cuente de qué se trata, adjunta una imagen representativa (puede ser una captura de pantalla del juego o un diseño relacionado) y finalmente pega el enlace que Genially te proporcionó. ¡Listo! Con eso ya podrás visualizar tu juego dentro de nuestra plataforma y compartirlo con los demás.</p>
                        <div class="info-btns">
                            <a href="https://genially.com/es/" class="btn btn-primary">Vamos a GENIALLY</a>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Form -->
                <div class="form-column">
                    <div class="form-wrapper">
                        <h2>Información del juego</h2>
                        
                        <?php if(isset($_SESSION['success_message'])): ?>
                            <div class="alert success">
                                <?php 
                                echo $_SESSION['success_message']; 
                                unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['error_message'])): ?>
                            <div class="alert error">
                                <?php 
                                echo $_SESSION['error_message']; 
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="gameForm" action="guardar_juego.php" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="gameName">Nombre<span class="required">*</span></label>
                                <input type="text" id="gameName" name="gameName" placeholder="Ingresa el nombre del juego" required oninput="this.value = this.value.toUpperCase()">
                                <small class="error-msg" id="gameNameError"></small>
                            </div>
                            
                            <div class="form-group">
                                <label for="gameDescription">Descripción <span class="required">*</span></label>
                                <textarea id="gameDescription" name="gameDescription" rows="4" placeholder="Describe el propósito y funcionamiento del juego" required></textarea>
                                <small class="error-msg" id="gameDescriptionError"></small>
                            </div>
                            
                            <div class=".form-group label img">
                                <label for="gameImage">Portada del juego <span class="required">*</span></label>
                                <div class="file-upload">
                                    <input type="file" id="gameImage" name="gameImage" accept="image/*" required>
                                    <label for="gameImage" class="file-label">
                                        <i class="fas fa-cloud-upload-alt"></i> Seleccionar imagen
                                    </label>
                                    <span class="file-name" id="fileName">Ningún archivo seleccionado</span>
                                </div>
                                <small class="error-msg" id="gameImageError"></small>
                            </div>
                            
                            <div class="form-group">
                                <label for="gameUrl">Link del juego <span class="required">*</span></label>
                                <input type="url" id="gameUrl" name="gameUrl" placeholder="https://ejemplo.com/mi-juego" required>
                                <small class="error-msg" id="gameUrlError"></small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-submit">Crear juego</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p>&copy; 2024 SABERQUEST. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript for form validation -->
    <script src="../../assets/src_juegos/js/validation.js"></script>
</body>
</html>
