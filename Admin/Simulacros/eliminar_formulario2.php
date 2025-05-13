<?php
include("../../base de datos/con_db.php");

// Verificar que se haya enviado el ID del formulario
if (isset($_POST['formulario_id']) && is_numeric($_POST['formulario_id'])) {
    $formulario_id = $_POST['formulario_id'];
    
    // Comenzar una transacción para asegurar la integridad de los datos
    $conex->begin_transaction();
    
    try {
        // Primero eliminamos todas las preguntas asociadas
        $stmt_preguntas = $conex->prepare("DELETE FROM preguntas WHERE formulario_id = ?");
        $stmt_preguntas->bind_param("i", $formulario_id);
        $stmt_preguntas->execute();
        
        // Luego eliminamos el formulario
        $stmt_formulario = $conex->prepare("DELETE FROM formularios WHERE id = ?");
        $stmt_formulario->bind_param("i", $formulario_id);
        $stmt_formulario->execute();
        
        // Si todo está bien, confirmamos la transacción
        $conex->commit();
        
        // Redirigir a la lista de formularios con un mensaje de éxito
        header("Location: formularios.php?mensaje=formulario_eliminado");
        exit;
    } catch (Exception $e) {
        // Si algo falla, revertimos los cambios
        $conex->rollback();
        
        // Redirigir con mensaje de error
        header("Location: formularios.php?error=eliminar_formulario");
        exit;
    }
} else {
    // Si no se proporcionó un ID válido, redirigir a la lista de formularios
    header("Location: formularios.php");
    exit;
}
?>