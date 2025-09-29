<?php
session_start();
include("../../base de datos/con_db.php");

// Validar que el usuario esté logueado y sea profesor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'Administrador') {
    header('Location: ../../index.php');
    exit;
}

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

    <style>
    /* Estilos de carrusel reutilizados de ver_formulario.php */
    .pregunta-imagen-unica { margin: 10px 0 5px 0; }
    .pregunta-imagen-unica img {
        max-width: 100%; width: 100%; max-height: 320px; height: auto;
        border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        object-fit: contain; background: #fff;
    }
    .carousel {
        position: relative; margin: 10px 0 5px 0; overflow: hidden;
        border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .carousel-track { display: flex; transition: transform 0.35s ease; will-change: transform; }
    .carousel-slide { min-width: 100%; display: flex; align-items: center; justify-content: center; background: #fff; }
    .carousel-slide img { max-width: 100%; width: 100%; max-height: 360px; height: auto; object-fit: contain; background: #fff; }
    .carousel-btn {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 36px; height: 36px; border-radius: 50%; border: 1px solid #E0E0E0;
        background: rgba(255,255,255,0.9); color: #003366; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.05); z-index: 2;
    }
    .carousel-btn:hover { background: #003366; color: #fff; border-color: #003366; transform: translateY(-50%) scale(1.05); }
    .carousel-btn:disabled { opacity: .5; cursor: not-allowed; }
    .carousel-btn.prev { left: 10px; }
    .carousel-btn.next { right: 10px; }
    .carousel-dots { position: absolute; left: 50%; transform: translateX(-50%); bottom: 8px; display: flex; gap: 6px; z-index: 1; }
    .carousel-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(0,0,0,0.15); border: none; cursor: pointer; transition: all 0.3s ease; }
    .carousel-dot:hover { background: rgba(0,0,0,0.3); }
    .carousel-dot.active { background: #003366; }
    </style>
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

                <form id="form-builder" action="guardar_formulario.php" method="POST" enctype="multipart/form-data"
                    novalidate>
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
                                    <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
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

                            <div class="form-group">
                                <label for="question-image-1">Imagen(es) de la pregunta</label>
                                <div class="image-upload-container">
                                    <input type="file" id="question-image-1" name="imagen_pregunta[0][]"
                                        accept="image/*" multiple class="file-input" style="display:none;">
                                    <button type="button" class="btn btn-upload" data-target="question-image-1">
                                        <i class="fas fa-image"></i> <span class="btn-text">Insertar imagen</span>
                                    </button>
                                    <div id="question-image-preview-container-1" class="image-preview-container hidden">
                                        <img id="question-image-preview-1" src="#" alt="Vista previa de la imagen">
                                        <button type="button" class="btn btn-delete btn-remove-image"
                                            data-target="question-image-1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="options-container">
                                <div class="option-row">
                                    <div class="option-label">a)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-a-1" name="option_a[]" placeholder="Opción a">
                                        <div class="image-upload-container">
                                            <input type="file" id="option-a-image-1" name="imagen_opcion_a[]"
                                                accept="image/*" class="file-input" style="display:none;">
                                            <button type="button" class="btn btn-upload" data-target="option-a-image-1">
                                                <i class="fas fa-image"></i> <span class="btn-text">Insertar
                                                    imagen</span>
                                            </button>
                                            <div id="option-a-image-preview-container-1"
                                                class="image-preview-container hidden">
                                                <img id="option-a-image-preview-1" src="#"
                                                    alt="Vista previa de la imagen">
                                                <button type="button" class="btn btn-delete btn-remove-image"
                                                    data-target="option-a-image-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">b)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-b-1" name="option_b[]" placeholder="Opción b">
                                        <div class="image-upload-container">
                                            <input type="file" id="option-b-image-1" name="imagen_opcion_b[]"
                                                accept="image/*" class="file-input" style="display:none;">
                                            <button type="button" class="btn btn-upload" data-target="option-b-image-1">
                                                <i class="fas fa-image"></i> <span class="btn-text">Insertar
                                                    imagen</span>
                                            </button>
                                            <div id="option-b-image-preview-container-1"
                                                class="image-preview-container hidden">
                                                <img id="option-b-image-preview-1" src="#"
                                                    alt="Vista previa de la imagen">
                                                <button type="button" class="btn btn-delete btn-remove-image"
                                                    data-target="option-b-image-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">c)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-c-1" name="option_c[]" placeholder="Opción c">
                                        <div class="image-upload-container">
                                            <input type="file" id="option-c-image-1" name="imagen_opcion_c[]"
                                                accept="image/*" class="file-input" style="display:none;">
                                            <button type="button" class="btn btn-upload" data-target="option-c-image-1">
                                                <i class="fas fa-image"></i> <span class="btn-text">Insertar
                                                    imagen</span>
                                            </button>
                                            <div id="option-c-image-preview-container-1"
                                                class="image-preview-container hidden">
                                                <img id="option-c-image-preview-1" src="#"
                                                    alt="Vista previa de la imagen">
                                                <button type="button" class="btn btn-delete btn-remove-image"
                                                    data-target="option-c-image-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="option-row">
                                    <div class="option-label">d)</div>
                                    <div class="form-group option-input">
                                        <input type="text" id="option-d-1" name="option_d[]" placeholder="Opción d">
                                        <div class="image-upload-container">
                                            <input type="file" id="option-d-image-1" name="imagen_opcion_d[]"
                                                accept="image/*" class="file-input" style="display:none;">
                                            <button type="button" class="btn btn-upload" data-target="option-d-image-1">
                                                <i class="fas fa-image"></i> <span class="btn-text">Insertar
                                                    imagen</span>
                                            </button>
                                            <div id="option-d-image-preview-container-1"
                                                class="image-preview-container hidden">
                                                <img id="option-d-image-preview-1" src="#"
                                                    alt="Vista previa de la imagen">
                                                <button type="button" class="btn btn-delete btn-remove-image"
                                                    data-target="option-d-image-1">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
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
    <style>.swal2-title::after{content:none!important;}</style>

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