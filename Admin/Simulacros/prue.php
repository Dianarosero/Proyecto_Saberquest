<?php
// Include configuration and database connection
include("../../base de datos/con_db.php");
$formulario_id = $_GET['id'] ?? 0;

$stmt = $conex->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$stmt->bind_param("i", $formulario_id);
$stmt->execute();
$stmt->bind_result($titulo, $descripcion);
$stmt->fetch();
$stmt->close();

if ($conex->num_rows === 0) {
    $form = null;
} else {
    $form = $conex->fetch_assoc();
}

// Obtener preguntas (siguiendo el ejemplo proporcionado)
$questions = [];
if ($form) {
    $stmt = $conex->prepare("SELECT enunciado, opciones, correcta FROM preguntas WHERE formulario_id = ? ORDER BY question_order ASC");
    $stmt->bind_param("i", $formulario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $conex->fetch_assoc()) {
        $questions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $form ? htmlspecialchars($form['title']) : 'Formulario no encontrado'; ?> - Universidad CESMAG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php if ($form): ?>
        <?php 
        // Get background image URL or use default static image
        $bg_image = !empty($form['background_image']) ? $form['background_image'] : 'https://pixabay.com/get/g8386919d873394672d9c4f2b4a58bfdf6ddbc88918bd7be5af792f69144340e15c8134ca7a5df3c89411ba0b9f15bc66048659caff8143cbeee94118364b59da_1280.jpg';
        ?>
        <div class="bg-container" style="background-image: url('<?php echo htmlspecialchars($bg_image); ?>')"></div>
        
        <header class="form-header">
            <div class="university-logo">Universidad CESMAG</div>
            <div class="mode-toggle">
                <button id="editModeBtn" class="btn btn-primary">Editar</button>
            </div>
        </header>
        
        <div class="container">
            <div id="viewMode" class="form-container">
                <div class="form-content">
                    <h1 id="formTitle"><?php echo htmlspecialchars($form['title']); ?></h1>
                    <p id="formDescription"><?php echo nl2br(htmlspecialchars($form['description'])); ?></p>
                    
                    <div class="pagination-info" id="paginationInfo"></div>
                    
                    <hr>
                    
                    <div id="questionsContainer">
                        <?php 
                        $num = 1;
                        foreach ($questions as $question):
                            // Obtener opciones para esta pregunta
                            $stmt = $conn->prepare("SELECT id, text FROM options WHERE question_id = ? ORDER BY option_order ASC");
                            $stmt->bind_param("i", $question['id']);
                            $stmt->execute();
                            $options_result = $stmt->get_result();
                            
                            $options = [];
                            $letters = ['a', 'b', 'c', 'd'];
                            $i = 0;
                            
                            while ($option = $options_result->fetch_assoc()) {
                                if ($i < count($letters)) {
                                    $options[$letters[$i]] = $option;
                                    $i++;
                                }
                            }
                        ?>
                            <div class="question-container" data-question-id="<?php echo htmlspecialchars($question['id']); ?>">
                                <strong><?php echo $num++; ?>. <?php echo htmlspecialchars($question['text']); ?></strong>
                                
                                <?php foreach ($letters as $letter):
                                    if (isset($options[$letter])):
                                        $isCorrect = ($question['correct_option_id'] == $options[$letter]['id']);
                                        $correctClass = $isCorrect ? ' correct-answer' : '';
                                ?>
                                    <div class="option-item<?php echo $correctClass; ?>">
                                        <input type="radio" id="option_<?php echo $options[$letter]['id']; ?>" 
                                               name="question_<?php echo $question['id']; ?>" 
                                               <?php echo $isCorrect ? 'checked' : ''; ?> disabled>
                                        <label for="option_<?php echo $options[$letter]['id']; ?>">
                                            <?php echo $letter; ?>) <?php echo htmlspecialchars($options[$letter]['text']); ?>
                                        </label>
                                    </div>
                                <?php endif; endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-navigation">
                        <button id="prevBtn" class="btn btn-secondary" style="display: none;">Anterior</button>
                        <button id="nextBtn" class="btn btn-secondary" style="display: none;">Siguiente</button>
                        <button id="submitBtn" class="btn btn-primary" disabled>Enviar</button>
                    </div>
                </div>
            </div>
            
            <div id="editMode" class="form-container" style="display: none;">
                <div class="form-content">
                    <div class="edit-form-header">
                        <div class="form-title-edit">
                            <label for="editFormTitle">Título del formulario</label>
                            <input type="text" id="editFormTitle" value="<?php echo htmlspecialchars($form['title']); ?>">
                        </div>
                        <div class="form-description-edit">
                            <label for="editFormDescription">Descripción</label>
                            <textarea id="editFormDescription"><?php echo htmlspecialchars($form['description']); ?></textarea>
                        </div>
                        <div class="form-background-edit">
                            <label for="editFormBackground">Imagen de fondo</label>
                            <input type="file" id="editFormBackground" accept="image/*">
                            <div class="current-bg-preview">
                                <span>Fondo actual:</span>
                                <img src="<?php echo htmlspecialchars($bg_image); ?>" alt="Fondo actual" class="bg-thumbnail" onerror="this.src='https://via.placeholder.com/80x60?text=No+Image'">
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div id="editQuestionsContainer">
                        <?php 
                        $num = 1;
                        foreach ($questions as $question):
                            // Obtener opciones para esta pregunta
                            $stmt = $conn->prepare("SELECT id, text FROM options WHERE question_id = ? ORDER BY option_order ASC");
                            $stmt->bind_param("i", $question['id']);
                            $stmt->execute();
                            $options_result = $stmt->get_result();
                            
                            $options = [];
                            $letters = ['a', 'b', 'c', 'd'];
                            $i = 0;
                            
                            while ($option = $options_result->fetch_assoc()) {
                                if ($i < count($letters)) {
                                    $options[$letters[$i]] = $option;
                                    $i++;
                                }
                            }
                        ?>
                            <div class="edit-question-container" data-question-id="<?php echo htmlspecialchars($question['id']); ?>">
                                <div class="edit-question-header">
                                    <label>Pregunta <?php echo $num++; ?></label>
                                </div>
                                <textarea class="edit-question-text" placeholder="Texto de la pregunta"><?php echo htmlspecialchars($question['text']); ?></textarea>
                                
                                <div class="edit-options-container">
                                    <label>Opciones de respuesta:</label>
                                    
                                    <?php foreach ($letters as $letter):
                                        if (isset($options[$letter])):
                                            $isCorrect = ($question['correct_option_id'] == $options[$letter]['id']);
                                    ?>
                                        <div class="edit-option-item" data-option-id="<?php echo htmlspecialchars($options[$letter]['id']); ?>">
                                            <input type="radio" class="correct-option" 
                                                   name="correct_<?php echo $question['id']; ?>" 
                                                   <?php echo $isCorrect ? 'checked' : ''; ?>>
                                            <label class="option-letter"><?php echo $letter; ?>)</label>
                                            <input type="text" class="edit-option-text" 
                                                   value="<?php echo htmlspecialchars($options[$letter]['text']); ?>" 
                                                   placeholder="Texto de la opción">
                                        </div>
                                    <?php endif; endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-edit-actions">
                        <button id="cancelEditBtn" class="btn btn-secondary">Cancelar</button>
                        <button id="saveFormBtn" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="form-footer">
            <p>&copy; <?php echo date('Y'); ?> Universidad CESMAG - Todos los derechos reservados</p>
        </footer>
        
        <input type="hidden" id="formId" value="<?php echo $form_id; ?>">
        <input type="hidden" id="totalQuestions" value="<?php echo count($questions); ?>">
        
    <?php else: ?>
        <header class="form-header">
            <div class="university-logo">Universidad CESMAG</div>
        </header>
        
        <div class="container">
            <div class="error-message">
                <h1>Formulario no encontrado</h1>
                <p>Lo sentimos, el formulario solicitado no existe o ha sido eliminado.</p>
                <a href="index.php" class="btn btn-primary">Volver al inicio</a>
            </div>
        </div>
        
        <footer class="form-footer">
            <p>&copy; <?php echo date('Y'); ?> Universidad CESMAG - Todos los derechos reservados</p>
        </footer>
    <?php endif; ?>
    
    <script src="js/script.js"></script>
</body>
</html>
