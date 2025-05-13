<?php
session_start();
include("../../base de datos/con_db.php");

// Verificar que se recibió el ID por método GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Preparar y ejecutar la consulta para eliminar el formulario
    $stmt = $conex->prepare("DELETE FROM formularios WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir a formularios.php con mensaje de éxito
        header("Location: formularios.php?msg=El formulario ha sido eliminado correctamente");
        exit();
    } else {
        // En caso de error
        header("Location: formularios.php?error=No se pudo eliminar el formulario");
        exit();
    }
} else {
    // Si no se recibe ID, redirigir
    header("Location: formularios.php");
    exit();
}
?>
