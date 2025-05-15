<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

session_start();
$mensaje = $_SESSION['mensaje'] ?? '';
$mensaje_tipo = $_SESSION['mensaje_tipo'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creador de Simulacros</title>
    <!-- Favicons -->
    <link href="../../assets/img/favicon.png" rel="icon">
    <link href="../../assets/img/favicon.png" rel="apple-touch-icon">
    <link rel="stylesheet" href="../../assets/src_simulacros/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <div class="logo">
                    <img width="120" height="50" fill="none" src="../../assets/img/Logo_fondoazul.png" alt="" srcset="">

                </div>
            </div>
            <a href="../index_admin.php#projects" class="btn-inicio">Inicio</a>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="form-creator">
                <h2>Crear nuevo simulacro</h2>

                <form id="form-builder" action="guardar_formulario.php" method="POST" enctype="multipart/form-data">
                    <div class="form-header">
                        <div class="form-group">
                            <label for="form-title">Título del simulacro</label>
                            <input type="text" id="form-title" name="titulo"
                                oninput="this.value = this.value.toUpperCase()"
                                placeholder="Ingrese el título del simulacro" required>
                        </div>

                        <div class="form-group">
                            <label for="form-description">Descripción</label>
                            <textarea id="form-description" name="descripcion"
                                placeholder="Ingrese una descripción para el simulacro" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="form-image">Imagen del simulacro</label>
                            <div class="image-upload-container">
                                <input type="file" id="form-image" name="imagen" accept="image/*" class="file-input"
                                    style="display:none;">
                                <button type="button" class="btn btn-upload" id="upload-image-btn">
                                    <i class="fas fa-image"></i> Insertar imagen
                                </button>
                                <div id="image-preview-container" class="image-preview-container hidden">
                                    <img id="image-preview" src="#" alt="Vista previa de la imagen">
                                    <button type="button" class="btn btn-delete btn-remove-image" id="remove-image-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Nuevo campo para mostrar resultados -->
                        <div class="form-group">
                            <label for="mostrar-resultados">Mostrar resultados a estudiantes</label>
                            <input type="checkbox" id="mostrar-resultados" name="mostrar_resultados" value="1">
                            <span>Permitir que los estudiantes vean los resultados</span>
                        </div>
                    </div>

                    <div class="questions-container" id="questions-container">
                        <!-- Initial question template -->
                        <div class="question-card" data-question-id="1">
                            <div class="question-header">
                                <h3>Pregunta 1</h3>
                                <button type="button" class="btn btn-delete" onclick="deleteQuestion(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="form-group">
                                <label for="question-text-1">Texto de la pregunta</label>
                                <input type="text" id="question-text-1" name="enunciado[]"
                                    placeholder="Escriba su pregunta aquí" required>
                            </div>

                            <div class="options-container">
                                <div class="option-row">
                                    <div class="option-label">a)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-a-1" name="option_a[]" placeholder="Opción a"
                                            required>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">b)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-b-1" name="option_b[]" placeholder="Opción b"
                                            required>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">c)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-c-1" name="option_c[]" placeholder="Opción c"
                                            required>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">d)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-d-1" name="option_d[]" placeholder="Opción d"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="correct-answer">
                                <label for="correct-answer-1">Respuesta correcta:</label>
                                <select id="correct-answer-1" name="correcta[]" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="a">a</option>
                                    <option value="b">b</option>
                                    <option value="c">c</option>
                                    <option value="d">d</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-add" id="add-question">
                            <i class="fas fa-plus"></i> Añadir pregunta
                        </button>

                        <button type="button" class="btn btn-preview" id="preview-button">
                            <i class="fas fa-eye"></i> Vista previa
                        </button>

                        <button type="submit" class="btn btn-save">
                            <i class="fas fa-save"></i> Guardar simulacro
                        </button>
                    </div>
                </form>
            </div>

            <div class="form-preview">

                <div class="preview-info">
                    <h3>Creador de simulacros</h3>
                    <p>Crea simulacros personalizados para evaluar conocimientos.</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Añade múltiples preguntas</li>
                        <li><i class="fas fa-check"></i> Opciones de respuesta múltiple</li>
                        <li><i class="fas fa-check"></i> Identifica respuestas correctas</li>
                    </ul>
                </div>

            </div>

        </div>
    </main>

    <footer>
        <div class="footer-container">
            <p>&copy; 2025 SABERQUEST. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Modal de vista previa -->
    <div class="modal" id="preview-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Vista previa del simulacro</h3>
                <button type="button" class="close-modal" id="close-preview-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-preview-container">
                    <div class="preview-header">
                        <h3 id="preview-title">Título del simulacro</h3>
                        <p id="preview-description">Descripción del simulacro</p>
                        <div id="preview-image-container" class="preview-image-box hidden">
                            <img id="preview-image" src="#" alt="Imagen del simulacro">
                        </div>
                    </div>
                    <div class="preview-questions" id="preview-questions">
                        <!-- Las preguntas se generarán dinámicamente aquí -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/src_simulacros/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    const mensaje = <?php echo json_encode($mensaje); ?>;
    const tipo = <?php echo json_encode($mensaje_tipo); ?>;

    if (mensaje) {
        Swal.fire({
            icon: tipo || 'info', // 'success', 'error', 'warning', 'info', 'question'
            title: mensaje,
            confirmButtonText: 'Aceptar'
        });
    }
    </script>
</body>

</html>