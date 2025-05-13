<?php
session_start();
include("../../base de datos/con_db.php");

// Verificar si se recibió el ID
if (!isset($_POST['id']) || empty($_POST['id'])) {
    $_SESSION['error_message'] = "ID de juego no proporcionado.";
    header("Location: ver_juegos.php");
    exit();
}

$id = $_POST['id'];

// Eliminar el juego
$query = "DELETE FROM juegos WHERE id = ?";
$stmt = $conex->prepare($query);
if (!$stmt) {
    $_SESSION['error_message'] = "Error en la preparación de la consulta: " . $conex->error;
    header("Location: ver_juegos.php");
    exit();
}

$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    $_SESSION['success_message'] = "Juego eliminado correctamente.";
} else {
    $_SESSION['error_message'] = "Error al eliminar el juego: " . $stmt->error;
}

$stmt->close();

// Redirigir de vuelta a la página de juegos
header("Location: ver_juegos.php");
exit();
?>